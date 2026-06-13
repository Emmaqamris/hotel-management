<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateEmployee
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!auth('employee')->check()) {
            return redirect()->route('login');
        }
        return $next($request);
    }
}
