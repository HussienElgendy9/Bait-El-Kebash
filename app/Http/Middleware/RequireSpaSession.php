<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireSpaSession
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->hasSession(), 419, 'Initialize CSRF cookies and send a configured first-party Origin.');

        return $next($request);
    }
}
