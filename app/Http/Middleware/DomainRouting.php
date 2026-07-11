<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DomainRouting
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        $baseDomain = config('app.base_domain', 'dinofy.cloud');
        $path = $request->path();

        if ($host === 'localhost' || str_starts_with($host, '127.') || str_starts_with($host, '192.168.')) {
            return $next($request);
        }

        if (str_starts_with($host, 'admin.')) {
            if ($path === '/' || $path === '') {
                return redirect('/admin');
            }
            if (str_starts_with($path, 'client') || str_starts_with($path, 'checkout')) {
                return redirect("https://master.{$baseDomain}/{$path}");
            }
        }

        if (str_starts_with($host, 'master.')) {
            if ($path === '/' || $path === '') {
                return redirect('/login');
            }
            if (str_starts_with($path, 'admin')) {
                return redirect("https://admin.{$baseDomain}/{$path}");
            }
        }

        return $next($request);
    }
}
