<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // get api key from header request
        $apiKey = $request->header('X-API-KEY');

        // validate api key
        if ($apiKey !== config('services.api_key')) {
            return response()->json(['error' => 'Unauthorized key invalid'], 401);
        }

        return $next($request);
    }
}
