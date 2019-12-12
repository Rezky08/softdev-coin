<?php

namespace App\Http\Controllers\Coin;

use App\Helpers\Currency as currency;
use App\Http\Controllers\Controller;

use App\Model\CoinDetail as coin_details;
use App\Model\CoinTransaction as coin_transactions;
use App\Model\CustomerDetail as customer_details;
use App\Model\SellerShop as seller_shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CoinTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // Get request
        if ($request->has('coin_transactions')) {
            $coinTransactions = $request->coin_transactions;
        } else {
            // validate Request
            $validation = Validator::make($request->all(), [
                'username_source' => ['required'],
                'username_destination' => ['required'],
                'transaction_balance' => ['required', 'numeric', 'min:0']
            ]);
            if ($validation->fails()) {
                $response = [
                    'status' => 400,
                    'message' => $validation->errors()
                ];
                return response()->json($response, 400);
            }
            //

            // preparation
            $coinTransactions[] = (object) [
                'username_source' => $request->username_source,
                'username_destination' => $request->username_destination,
                'transaction_balance' => $request->transaction_balance
            ];
            //
        }
        //

        $coinTransactions = collect($coinTransactions);
        $coinUsername = $coinTransactions->map(function ($item) {
            $item = collect($item);
            $item = $item->except(['transaction_balance']);
            return $item;
        });

        // get coin data
        $coin = new CoinRegisterController;
        $status = $coin->showByUsername($coinUsername);
        if ($status->getStatusCode() != 200) {
            return $status;
        }
        $status = json_decode($status->getContent())->data;
        $status = collect($status);
        $coinDetails = $status;
        //

        $coinTransactions = $coinTransactions->map(function ($item) use ($coinDetails) {
            $item = (object) $item;
            // get coin source
            $status = $coinDetails->where('username', $item->username_source);
            if ($status->isEmpty()) {
                $response = [
                    'status' => 400,
                    'message' => 'User Not Found',
                    'data' => $item->username_source
                ];
                return response()->json($response, 400);
            }
            $status = $status->first();
            $prep['coin_id_source'] = $status->id;
            //

            // get coin destination
            $status = $coinDetails->where('username', $item->username_destination);
            if ($status->isEmpty()) {
                $response = [
                    'status' => 400,
                    'message' => 'User Not Found',
                    'data' => $item->username_destination
                ];
                return response()->json($response, 400);
            }
            $status = $status->first();
            $prep['coin_id_destination'] = $status->id;
            //
            $prep['coin_balance'] = $item->transaction_balance;
            $prep = (object) $prep;
            return $prep;
        });


        $credit = $coinTransactions->map(function ($item, $key) {
            $item = (object) [
                'coin_id_source' => $item->coin_id_destination,
                'coin_id_destination' => $item->coin_id_source,
                'coin_transaction_code' => 1,
                'coin_transaction_type' => 0, //credit
                'coin_balance' => $item->coin_balance,
                'created_at' => date_format(now(), 'Y-m-d H:i:s'),
                'updated_at' => date_format(now(), 'Y-m-d H:i:s'),
            ];
            return $item;
        });
        $debit = $credit->map(function ($item) {
            $item = (object) [
                'coin_id_source' => $item->coin_id_destination,
                'coin_id_destination' => $item->coin_id_source,
                'coin_transaction_code' => 1,
                'coin_transaction_type' => 1, // debit
                'coin_balance' => $item->coin_balance,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
            return $item;
        });
        $transactions = $debit->concat($credit);
        $transactions = $transactions->map(function ($item) {
            $item = collect($item);
            $item = $item->toArray();
            return $item;
        });
        $status = coin_transactions::insert($transactions->all());
        if (!$status) {
            $response = [
                'status' => 500,
                'message' => 'Internal Server Error'
            ];
            return response()->json($response, 500);
        }

        $coinBalance = new CoinBalanceController;
        $debit->map(function ($item) use ($coinBalance) {
            $status = $coinBalance->balanceDebit($item->coin_id_source, $item->coin_balance);
        });
        $credit->map(function ($item) use ($coinBalance) {
            $status = $coinBalance->balanceCredit($item->coin_id_source, $item->coin_balance);
        });

        $response = [
            'status' => 200,
            'message' => 'Transaction has been created'
        ];
        return response()->json($response, 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeSellerSupplierTransaction(Request $request)
    {
        $supplierTransactionIds = $request->supplier_seller_transaction_ids;
        $supplierTransactionIds = $supplierTransactionIds->flatten();

        // get supplier transactions
        $supplierTransactions = new SupplierSellerTransactionController;
        $supplierTransactions = $supplierTransactions->show($supplierTransactionIds);
        if ($supplierTransactions->getStatusCode() != 200) {
            return $supplierTransactions;
        }
        $supplierTransactions = json_decode($supplierTransactions->getContent());
        $supplierTransactions = collect($supplierTransactions->data);
        //

        // get only id suppliers and sellers
        $supplierIds = $supplierTransactions->mapToGroups(function ($item) {
            return [$item->id => $item->supplier_id];
        });
        $sellerIds = $supplierTransactions->mapToGroups(function ($item) {
            return [$item->id => $item->seller_id];
        });
        //

        // get suppliers data
        $suppliers = new SupplierRegisterController;
        $status = $suppliers->show($supplierIds);

        if ($status->getStatusCode() != 200) {
            return $status;
        }
        $suppliers = json_decode($status->getContent())->data;
        $suppliers = collect($suppliers);

        $suppliers = $suppliers->map(function ($item) use ($supplierTransactions) {
            $transaction = $supplierTransactions->where('supplier_id', $item->id)->first();
            $item->transaction_id = $transaction->id;
            return $item;
        });
        $suppliersUsernames = $suppliers->flatten()->map(function ($item) {
            return $item->username;
        });
        //

        // get sellers data
        $sellers = new SellerRegisterController;
        $status = $sellers->show($sellerIds);
        if ($status->getStatusCode() != 200) {
            return $status;
        }
        $sellers = json_decode($status->getContent())->data;
        $sellers = collect($sellers);
        $sellers = $sellers->first();
        //

        // get coin data data
        $coin = new CoinRegisterController;

        // get coin source data
        $status = $coin->showByUsername($sellers->username);
        if ($status->getStatusCode() != 200) {
            return $status;
        }
        $coin_source = json_decode($status->getContent())->data;
        $coin_source = collect($coin_source);
        $coin_source = $coin_source->first();
        //

        // get coin destination data
        $status = $coin->showByUsername($suppliersUsernames);
        if ($status->getStatusCode() != 200) {
            return $status;
        }
        $coin_destination = json_decode($status->getContent())->data;
        $coin_destination = collect($coin_destination);
        //

        $credit = $coin_destination->map(function ($item, $key) use ($suppliers, $coin_source, $supplierTransactions) {
            $transaction = $suppliers->where('username', $item->username)->first();
            $transaction = $supplierTransactions->where('id', $transaction->transaction_id)->first();
            $credit = [
                'coin_id_source' => $item->id,
                'coin_id_destination' => $coin_source->id,
                'coin_transaction_code' => 1,
                'coin_transaction_type' => 0,
                'coin_balance' => $transaction->supplier_total_price,
                'created_at' => date_format(now(), 'Y-m-d H:i:s'),
                'updated_at' => date_format(now(), 'Y-m-d H:i:s'),
            ];
            return $credit;
        });
        $debit = $credit->map(function ($item) {
            $source = $item['coin_id_destination'];
            $item['coin_id_destination'] = $item['coin_id_source'];
            $item['coin_id_source'] = $source;
            $item['coin_transaction_type'] = 1;
            return $item;
        });
        $transactions = $debit->concat($credit);
        $status = coin_transactions::insert($transactions->all());
        if (!$status) {
            $response = [
                'status' => 500,
                'message' => 'Internal Server Error'
            ];
            return response()->json($response, 500);
        }

        $coinBalance = new CoinBalanceController;
        $debit->map(function ($item) use ($coinBalance) {
            $status = $coinBalance->balanceDebit($item['coin_id_source'], $item['coin_balance']);
        });
        $credit->map(function ($item) use ($coinBalance) {
            $status = $coinBalance->balanceCredit($item['coin_id_source'], $item['coin_balance']);
        });

        $response = [
            'status' => 200,
            'message' => 'Transaction has been created'
        ];
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCustomerSellerTransaction(Request $request)
    {
        $sellerTransactionIds = $request->seller_customer_transaction_ids;
        $sellerTransactionIds = $sellerTransactionIds->flatten();

        // get seller transactions
        $sellerTransactions = new SellerCustomerTransactionController;
        $sellerTransactions = $sellerTransactions->show($sellerTransactionIds);
        if ($sellerTransactions->getStatusCode() != 200) {
            return $sellerTransactions;
        }
        $sellerTransactions = json_decode($sellerTransactions->getContent());
        $sellerTransactions = collect($sellerTransactions->data);
        //

        $sellersId = $sellerTransactions->mapToGroups(function ($item) {
            return [$item->id => $item->seller_shop_id];
        });
        $customersId = $sellerTransactions->mapToGroups(function ($item) {
            return [$item->id => $item->customer_id];
        });
        $sellers = seller_shops::whereIn('seller_shops.id', $sellersId)->rightJoin('seller_details', 'seller_shops.seller_id', '=', 'seller_details.id')->get(['seller_details.*', 'seller_shops.id as seller_shop_id']);

        $sellers = $sellers->map(function ($item) use ($sellerTransactions) {
            $transaction = $sellerTransactions->where('seller_shop_id', $item->seller_shop_id)->first();
            $item->transaction_id = $transaction->id;
            return $item;
        });
        $sellersUsernames = $sellers->flatten()->map(function ($item, $key) {
            return $item->seller_username;
        });
        $customers = customer_details::find($customersId)->first();

        $coin_source = coin_details::where('coin_username', $customers->customer_username)->get()->first();
        $coin_destination = coin_details::whereIn('coin_username', $sellersUsernames)->get();
        $credit = $coin_destination->map(function ($item, $key) use ($sellers, $coin_source, $sellerTransactions) {
            $transaction = $sellers->where('seller_username', $item->coin_username)->first();
            $transaction = $sellerTransactions->where('id', $transaction->transaction_id)->first();
            $credit = [
                'coin_id_source' => $item->id,
                'coin_id_destination' => $coin_source->id,
                'coin_transaction_code' => 1,
                'coin_transaction_type' => 0,
                'coin_balance' => $transaction->seller_total_price,
                'created_at' => date_format(now(), 'Y-m-d H:i:s'),
                'updated_at' => date_format(now(), 'Y-m-d H:i:s'),
            ];
            return $credit;
        });
        $debit = $credit->map(function ($item) {
            $source = $item['coin_id_destination'];
            $item['coin_id_destination'] = $item['coin_id_source'];
            $item['coin_id_source'] = $source;
            $item['coin_transaction_type'] = 1;
            return $item;
        });
        $transactions = $debit->concat($credit);
        $status = coin_transactions::insert($transactions->all());
        if (!$status) {
            $response = [
                'status' => 500,
                'message' => 'Internal Server Error'
            ];
            return response()->json($response, 500);
        }

        $coinBalance = new CoinBalanceController;
        $debit->map(function ($item) use ($coinBalance) {
            $status = $coinBalance->balanceDebit($item['coin_id_source'], $item['coin_balance']);
        });
        $credit->map(function ($item) use ($coinBalance) {
            $status = $coinBalance->balanceCredit($item['coin_id_source'], $item['coin_balance']);
        });

        $response = [
            'status' => 200,
            'message' => 'Transaction has been created'
        ];
        return response()->json($response, 200);
    }

    public function storeTopUpTransaction(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'topup_transaction' => ['required'],
            'coin_detail' => ['required']
        ]);
        if ($validation->fails()) {
            $response = [
                'status' => 400,
                'message' => $validation->errors()
            ];
            return response()->json($response, 400);
        }
        $status = coin_transactions::insert($request->topup_transaction);
        $currency = new currency;
        $response = [
            'status' => 200,
            'message' => 'Topup of IDR ' . $currency->intToIdr($request->topup_transaction['coin_balance']) . ' to ' . $request->coin_detail->username . ' successfully'
        ];
        return response()->json($response, 200);
    }




    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
