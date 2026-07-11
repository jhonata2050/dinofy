<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DomainRouting
{
    public function handle(Request $request, Closure $next)
    {
        $path = $request->path();

        if ($path === '/' || $path === '') {
            return redirect('/login');
        }

        return $next($request);
    }
}
