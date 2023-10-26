<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ahc\Jwt\JWT;

class MapGenerationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('token')) {
            abort(403);
        }

        $token = $request->input('token');
        $jwt = new JWT(env('APP_KEY'));

        try {
            $payload = $jwt->decode($token);
        } catch (\Exception $exception) {
            abort(403);
        }

        return $next($request);
    }
}
