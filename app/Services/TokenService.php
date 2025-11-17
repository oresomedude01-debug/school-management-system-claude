<?php

namespace App\Services;

use App\Models\RegistrationToken;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class TokenService
{
    /**
     * Generate a single registration token
     *
     * @param User $generatedBy
     * @param array $options
     * @return RegistrationToken
     */
    public function generateToken(User $generatedBy, array $options = []): RegistrationToken
    {
        $tokenCode = $this->generateUniqueTokenCode();

        return RegistrationToken::create([
            'token_code' => $tokenCode,
            'status' => 'unused',
            'generated_by' => $generatedBy->id,
            'generated_at' => now(),
            'expires_at' => $options['expires_at'] ?? null,
            'notes' => $options['notes'] ?? null,
        ]);
    }

    /**
     * Generate multiple registration tokens
     *
     * @param User $generatedBy
     * @param int $count
     * @param array $options
     * @return Collection
     */
    public function generateTokens(User $generatedBy, int $count, array $options = []): Collection
    {
        $tokens = collect();

        for ($i = 0; $i < $count; $i++) {
            $tokens->push($this->generateToken($generatedBy, $options));
        }

        return $tokens;
    }

    /**
     * Generate a unique token code
     *
     * Format: ENROLL-{YEAR}-{RANDOM8}
     * Example: ENROLL-2024-A7F3B9C2
     *
     * @return string
     */
    protected function generateUniqueTokenCode(): string
    {
        $year = now()->year;

        do {
            $randomPart = strtoupper(Str::random(8));
            $tokenCode = "ENROLL-{$year}-{$randomPart}";
        } while (RegistrationToken::where('token_code', $tokenCode)->exists());

        return $tokenCode;
    }

    /**
     * Validate a token by code
     *
     * @param string $tokenCode
     * @return array [bool $isValid, RegistrationToken|null $token, string|null $error]
     */
    public function validateToken(string $tokenCode): array
    {
        $token = RegistrationToken::where('token_code', $tokenCode)->first();

        if (!$token) {
            return [
                'is_valid' => false,
                'token' => null,
                'error' => 'Token not found or invalid.',
            ];
        }

        if ($token->status === 'used') {
            return [
                'is_valid' => false,
                'token' => $token,
                'error' => 'This token has already been used.',
            ];
        }

        if ($token->status === 'disabled') {
            return [
                'is_valid' => false,
                'token' => $token,
                'error' => 'This token has been disabled.',
            ];
        }

        if ($token->status === 'expired') {
            return [
                'is_valid' => false,
                'token' => $token,
                'error' => 'This token has expired.',
            ];
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            // Auto-mark as expired
            $token->update(['status' => 'expired']);
            return [
                'is_valid' => false,
                'token' => $token,
                'error' => 'This token has expired.',
            ];
        }

        return [
            'is_valid' => true,
            'token' => $token,
            'error' => null,
        ];
    }

    /**
     * Disable a token
     *
     * @param RegistrationToken $token
     * @return bool
     */
    public function disableToken(RegistrationToken $token): bool
    {
        if ($token->status !== 'unused') {
            return false;
        }

        $token->disable();
        return true;
    }

    /**
     * Mark token as used
     *
     * @param RegistrationToken $token
     * @param \App\Models\Student $student
     * @return void
     */
    public function markAsUsed(RegistrationToken $token, $student): void
    {
        $token->markAsUsed($student);
    }

    /**
     * Get token statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total' => RegistrationToken::count(),
            'unused' => RegistrationToken::where('status', 'unused')->count(),
            'used' => RegistrationToken::where('status', 'used')->count(),
            'disabled' => RegistrationToken::where('status', 'disabled')->count(),
            'expired' => RegistrationToken::where('status', 'expired')->count(),
        ];
    }
}
