<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;

class checkScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        $guard = ['customer', 'seller', 'supplier'];
        foreach ($scopes as $scopesTemplate) {
            $user = Auth::guard($scopesTemplate)->user();
            if ($user) {
                if ($user->tokenCan($scopesTemplate)) {
                    $request->request->add([$scopesTemplate . 'Data' => $user]);
                    return $next($request);
                }
            }
        }
        $response = [
            'status' => 401,
            'Message' => "You're Not Authorized"
        ];
        return response()->json($response, 401);
    }
}
