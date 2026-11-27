<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends browsers on an old address (APP_LEGACY_HOSTS, e.g.
 * isidulu.bag-itd.my.id) to APP_URL, which has to be under al-azhar.id for
 * the shared passkeys. Kehadiran's roster push still posts to the old host,
 * because Cloudflare's bot protection on al-azhar.id blocks server calls.
 */
class RedirectLegacyHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $legacy = array_filter(array_map('trim', explode(',', (string) config('app.legacy_hosts'))));

        if (in_array(strtolower($request->getHost()), array_map('strtolower', $legacy), true)
            && $request->isMethodSafe()
            && ! $request->is('kehadiran/sync')) {
            return redirect()->away(rtrim((string) config('app.url'), '/').$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
