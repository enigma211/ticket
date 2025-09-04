<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CooldownMessage
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && ($user->is_agent || $user->is_superadmin)) {
            return $next($request);
        }

        $minutes = max(1, (int) (settings('cooldown_message_minutes') ?? 15));
        $key = 'cooldown:message:' . ($user?->id ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['error' => 'لطفاً ' . $seconds . ' ثانیه صبر کنید و سپس دوباره تلاش کنید.'])->withInput();
        }

        RateLimiter::hit($key, $minutes * 60);
        return $next($request);
    }
}


