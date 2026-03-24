<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SwaggerAccess
{
    public function handle(Request $request, Closure $next)
    {
        // Only allow Swagger in local and staging environments
        if (app()->environment('production')) {
            abort(404);
        }

        return $next($request);
    }
}
