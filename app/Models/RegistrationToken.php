<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_code',
        'status',
        'generated_by',
        'generated_at',
        'used_at',
        'used_by_student_id',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'used_by_student_id');
    }

    /**
     * Scopes
     */

    public function scopeUnused($query)
    {
        return $query->where('status', 'unused');
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    public function scopeDisabled($query)
    {
        return $query->where('status', 'disabled');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'unused')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Check if token is valid for use
     */
    public function isValid(): bool
    {
        if ($this->status !== 'unused') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Mark token as used
     */
    public function markAsUsed(Student $student): void
    {
        $this->update([
            'status' => 'used',
            'used_at' => now(),
            'used_by_student_id' => $student->id,
        ]);
    }

    /**
     * Disable token
     */
    public function disable(): void
    {
        $this->update(['status' => 'disabled']);
    }
}
