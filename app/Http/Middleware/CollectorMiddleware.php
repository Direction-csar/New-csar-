<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CollectorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('collector_id')) {
            return redirect()->route('collector.login');
        }

        return $next($request);
    }
}
