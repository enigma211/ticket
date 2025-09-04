<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CooldownDownload
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && ($user->is_agent || $user->is_superadmin)) {
            return $next($request);
        }

        $minutes = max(1, (int) (settings('cooldown_attachment_minutes') ?? 2));
        $key = 'cooldown:download:' . ($user?->id ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return response('درخواست‌های بیش از حد. لطفاً ' . $seconds . ' ثانیه صبر کنید و دوباره تلاش کنید.', 429);
        }

        RateLimiter::hit($key, $minutes * 60);
        return $next($request);
    }
}


