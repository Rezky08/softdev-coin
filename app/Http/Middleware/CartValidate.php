<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Customer\CustomerCartController;
use App\Http\Controllers\Seller\SellerCartController;
use App\Model\CustomerCart as customer_carts;
use App\Model\SellerProduct as seller_products;
use Closure;

class CartValidate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $scope)
    {
        $userController = [
            'seller' => new SellerCartController,
            'customer' => new CustomerCartController,
        ];
        $scopeTemplate  = $scope . 'Data';
        $userData = $request->$scopeTemplate;
        if (!$userData) {
            $response = [
                'status' => 401,
                'Message' => "You're Not Authorized"
            ];
            return response()->json($response, 401);
        }
        $userController = $userController[$scope];
        if ($request->has('cart_id')) {
            $cartValidation = $userController->cartValidation($request, $request->cart_id);
            $userCarts = $userController->showById($request, $request->cart_id);
        } else {
            $cartValidation = $userController->cartValidation($request);
            $userCarts = $userController->index($request);
        }
        if ($userCarts->getStatusCode() != 200) {
            return $userCarts;
        }
        if ($cartValidation->getStatusCode() != 200) {
            return $cartValidation;
        }

        $userCarts = json_decode($userCarts->getContent())->data;
        $userCarts = collect($userCarts);
        $products = json_decode($cartValidation->getContent())->data;
        $products = collect($products);

        // add to request
        $request->request->add([
            $scope . 'CartData' => $userCarts,
            'products' => $products
        ]);

        return $next($request);
    }
}
