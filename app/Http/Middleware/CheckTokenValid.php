<?php

namespace App\Http\Middleware;

use App\Services\TokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenValid
{
    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tokenCode = $request->route('token');

        if (!$tokenCode) {
            return redirect()->route('enrollment.error')
                ->with('error', 'No token provided.');
        }

        $validation = $this->tokenService->validateToken($tokenCode);

        if (!$validation['is_valid']) {
            return redirect()->route('enrollment.error')
                ->with('error', $validation['error']);
        }

        // Store token in request for later use
        $request->attributes->set('enrollment_token', $validation['token']);

        return $next($request);
    }
}
