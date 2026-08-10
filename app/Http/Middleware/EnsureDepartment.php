<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDepartment
{
    /**
     * Allow admins (access to everything) plus staff whose department
     * is listed in the accepted departments.
     */
    public function handle(Request $request, Closure $next, string ...$departments): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (in_array($user->department, $departments, true)) {
            return $next($request);
        }

        abort(403);
    }
}
