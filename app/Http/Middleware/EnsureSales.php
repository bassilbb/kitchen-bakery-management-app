<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSales
{
    /**
     * Allow admins (everything) and cashiers (sales) only.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin() || $user->isCashier()) {
            return $next($request);
        }

        abort(403);
    }
}
