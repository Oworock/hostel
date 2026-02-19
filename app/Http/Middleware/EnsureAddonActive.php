<?php

namespace App\Http\Middleware;

use App\Models\Addon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAddonActive
{
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        if (!Addon::isActive($slug)) {
            abort(404);
        }

        return $next($request);
    }
}
