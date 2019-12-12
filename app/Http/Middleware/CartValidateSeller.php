<?php

namespace App\Http\Middleware;

use App\Model\CustomerCart as customer_carts;
use App\Model\SellerProduct as seller_products;
use Closure;

class CartValidateSeller
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
        $customerData = $request->customerData;
        $whereCond = [
            'customer_id' => $customerData->id,
            'customer_status' => 0
        ];
        if ($request->has('cart_id')) {
            $customerCarts = customer_carts::where($whereCond)->whereIn('id', $request->cart_id)->get();
        } else {
            $customerCarts = customer_carts::where($whereCond);
            if (!$customerCarts->exists()) {
                $response = [
                    'status' => 400,
                    'message' => 'sorry, cannot find your product. Do want to buy something product?'
                ];
                return response()->json($response, 400);
            }
            $customerCarts = $customerCarts->get();
        }
        $productId = $customerCarts->map(function ($item) {
            return $item->customer_seller_product_id;
        });
        $products = seller_products::find($productId);

        // check product is availabe
        $productCheckId = $products->map(function ($item) {
            return $item->id;
        });
        $status = $productId->diff($productCheckId);
        if (!$status->isEmpty()) {
            $status = $customerCarts->whereIn('customer_seller_product_id', $status);
            $response = [
                'status' => 404,
                'message' => 'product not availabe',
                'data' => $status->all()
            ];
            return response()->json($response, 404);
        }
        $products = $products->groupBy('id');
        $status = $customerCarts->groupBy('customer_seller_product_id')->map(function ($item, $key) use ($products) {
            $item = $item[0];
            $products[$key] = $products[$key][0];
            if ($item->customer_product_qty > $products[$key]->seller_product_stock) {
                return $item->customer_product_name;
                // return (object) [$item->id => $item->customer_product_name];
            }
        });
        $status = $status->filter()->flatten();
        if (!$status->isEmpty()) {
            $response = [
                'status' => 400,
                'message' => 'out of stock',
                'data' => $status->all()
            ];
            return response()->json($response, 400);
        }

        // add to request
        $request->request->add([
            'customerCartData' => $customerCarts,
            'products' => $products
        ]);

        return $next($request);
    }
}
