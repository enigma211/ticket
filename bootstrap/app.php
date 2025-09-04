<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'is_superadmin' => \App\Http\Middleware\IsSuperAdmin::class,
            'is_agent' => \App\Http\Middleware\IsAgent::class,
            'cooldown.ticket' => \App\Http\Middleware\CooldownTicket::class,
            'cooldown.message' => \App\Http\Middleware\CooldownMessage::class,
            'cooldown.download' => \App\Http\Middleware\CooldownDownload::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        ]);
        // Do not append globally to avoid breaking UI; enable per-route or in groups later if needed
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
