<?php

namespace App\Http\Middleware;

use App\Model\CustomerDetail as customer_details;
use App\Model\CustomerLogin as customer_logins;
use App\Model\SellerDetail as seller_details;
use App\Model\SellerLogin as seller_logins;
use Closure;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;

class AuthAPI
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
        foreach (Passport::scopes() as $scopes) {
            if ($request->user()->tokenCan($scopes->id)) {
                if (Auth::guard($scopes->id)->user()) {
                    $request->request->add([$scopes->id . 'Data' => Auth::guard($scopes->id)->user()]);
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
