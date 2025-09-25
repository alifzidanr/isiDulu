<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAccessLevel
{
    public function handle(Request $request, Closure $next, ...$levels)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userLevel = auth()->user()->access_level;
        $allowedLevels = array_map('intval', $levels);

        if (!in_array($userLevel, $allowedLevels)) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}