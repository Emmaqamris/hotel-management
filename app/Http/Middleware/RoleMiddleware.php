<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $employee = auth('employee')->user();

        if (!$employee || !$employee->hasRole($roles)) {
            abort(403, "Unauthorized");
        }

        return $next($request);
    }
}
