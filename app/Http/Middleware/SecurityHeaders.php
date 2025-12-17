<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 1) Anti-clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // 2) Stop MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        $isLocal = app()->environment(['local', 'development']);

        if ($isLocal) {
        // Dev CSP, keep UI working
        $csp = "default-src 'self'; "
             . "base-uri 'self'; "
             . "object-src 'none'; "
             . "frame-ancestors 'none'; "
             . "img-src 'self' data:; "
             . "script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
             . "style-src 'self' 'unsafe-inline'; "
             . "connect-src 'self';";
        } else {
        // Prod CSP, stricter
        $csp = "default-src 'self'; "
            . "base-uri 'self'; "
            . "object-src 'none'; "
            . "frame-ancestors 'none'; "
            . "img-src 'self' data:; "
            . "script-src 'self'; "
            . "style-src 'self'; "
            . "connect-src 'self';";
        }

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
