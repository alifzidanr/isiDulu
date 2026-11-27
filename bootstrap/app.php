<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\CheckAccessLevel;
use App\Http\Middleware\RedirectLegacyHost;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\EnsurePasswordIsSet;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add your custom middleware alias here
        // Public traffic arrives through the Cloudflare tunnel connector
        // (192.168.3.2), so every visitor looked like that one IP and rate
        // limits were shared by everyone. Trust only that proxy and take the
        // visitor's IP and scheme from what it forwards.
        $middleware->trustProxies(
            at: array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', '192.168.3.2'))))),
            headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO,
        );
        $middleware->prepend(RedirectLegacyHost::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->appendToGroup('web', EnsurePasswordIsSet::class);

        $middleware->alias([
            'access.level' => CheckAccessLevel::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
