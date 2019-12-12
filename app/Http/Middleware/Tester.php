<?php

namespace App\Http\Middleware;

use Closure;

class Tester
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = [
            'status' => 200,
            'message' => 'Message'
        ];
        if (true) {
            return response()->json($response, 200);
        }
        // return $next($request);
    }
}
