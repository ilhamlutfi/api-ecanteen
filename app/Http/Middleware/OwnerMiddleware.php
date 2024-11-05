<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // check yang login harus/jika bukan owner
        if (!$request->user()->hasRole('owner')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        
        return $next($request);
    }
}
