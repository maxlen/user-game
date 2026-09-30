<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLinkIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $link = $request->route('accessLink');

        if ($link->isRevoked() || $link->isExpired()) {
            return response()->view('links.inactive', [], 410);
        }

        return $next($request);
    }
}
