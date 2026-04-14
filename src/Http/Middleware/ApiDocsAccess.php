<?php

namespace LaravelApiDocs\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiDocsAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('api-docs.enabled', true)) {
            abort(404);
        }

        return $next($request);
    }
}
