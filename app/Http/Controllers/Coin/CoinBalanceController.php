<?php

namespace App\Http\Controllers\Coin;

use App\Http\Controllers\Controller;


use App\Model\CoinBalance as coin_balances;
use App\Model\CoinDetail as coin_details;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CoinBalanceController extends Controller
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
        $coin_balance = $request->coin_balance;
        $status = coin_balances::insert($coin_balance->toArray());
        if (!$status) {
            $response = [
                'status' => 500,
                'message' => 'Internal Server Error'
            ];
            return response()->json($response, 500);
        }
        $response = [
            'status' => 200,
            'message' => 'Account balance created'
        ];
        return response()->json($response, 200);
    }

    public function show(Request $request)
    {
        if (!$request->has('id') && !$request->has('username')) {
            $response = [
                'status' => 400,
                'message' => 'field id or username required.'
            ];
            return response()->json($response, 400);
        }

        $validation = Validator::make($request->all(), [
            'id' => ['numeric'],
            'username' => ['string']
        ]);
        if ($validation->fails()) {
            $response = [
                'status' => 400,
                'message' => $validation->errors()
            ];
            return response()->json($response, 400);
        }

        if ($request->has('username')) {
            return $this->showByUsername($request->username);
        }
        if ($request->has('id')) {
            return $this->showById($request->id);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showByUsername(...$username)
    {
        // get coin details
        $coin = new CoinRegisterController;
        $status = $coin->showByUsername($username);
        if ($status->getStatusCode() != 200) {
            return $status;
        }
        $status = $status->getContent();
        $status = json_decode($status);
        $status = $status->data;
        $status = collect($status);
        $id = $status->map(function ($item) {
            return $item->id;
        });
        $id = $id->flatten()->toArray();
        return $this->showById($id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showById(...$id)
    {
        $coinID = collect($id);
        $coinID = $coinID->flatten();
        $coinDetails = coin_balances::whereIn('id', $coinID)->get();
        $coinIdCheck = $coinDetails->map(function ($item) {
            return $item->id;
        });
        $status = $coinID->diff($coinIdCheck);
        if (!$status->isEmpty()) {
            $response = [
                'status' => 404,
                'message' => 'Data not found',
                'data' => $status->all()
            ];
            return response()->json($response, 404);
        }
        $coinDetails = $coinDetails->map(function ($item) {
            $item = [
                'id' => $item->id,
                'coin_id' => $item->coin_id,
                'username' => $item->coin_username,
                'balance' => $item->coin_balance,
            ];
            return $item;
        });
        $response = [
            'status' => 200,
            'data' => $coinDetails
        ];
        return response()->json($response, 200);
    }

    public function validateBalance($username, $balance)
    {
        $coinData = coin_details::where('coin_details.coin_username', $username)->rightJoin('coin_balances', 'coin_details.id', '=', 'coin_balances.coin_id')->get()->first();
        $currentBalance = $coinData->coin_balance - $balance;
        if ($currentBalance < 0) {
            $response = [
                'status' => 400,
                'message' => 'The ballance is not sufficent'
            ];
            return response()->json($response, 400);
        }

        $response = [
            'status' => 200,
            'message' => 'sufficent balance'
        ];
        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function balanceDebit($coinId, $balance)
    {
        $whereCond = [
            'coin_id' => $coinId
        ];
        $coinBalance = coin_balances::where($whereCond)->first();
        $currentBalance = $coinBalance->coin_balance - $balance;
        if ($currentBalance < 0) {
            $response = [
                'status' => 400,
                'message' => 'The ballance is not sufficent'
            ];
            return response()->json($response, 400);
        }

        $status = coin_balances::where($whereCond)->update(['coin_balance' => $currentBalance]);
        if (!$status) {
            $response = [
                'status' => 500,
                'message' => 'Internal Server Error'
            ];
            return response()->json($response, 500);
        }

        $response = [
            'status' => 200,
            'message' => 'Balance has been updated'
        ];
        return response()->json($response, 200);
    }

    public function balanceCredit($coinId, $balance)
    {
        $whereCond = [
            'coin_id' => $coinId
        ];
        $coinBalance = coin_balances::where($whereCond)->get()->first();
        $currentBalance = $coinBalance->coin_balance + $balance;

        $status = coin_balances::where($whereCond)->update(['coin_balance' => $currentBalance]);
        if (!$status) {
            $response = [
                'status' => 500,
                'message' => 'Internal Server Error'
            ];
            return response()->json($response, 500);
        }

        $response = [
            'status' => 200,
            'message' => 'Balance has been updated'
        ];
        return response()->json($response, 200);
    }

    public function coinTopUp(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'username_destination' => ['required', 'exists:dbmarketcoins.coin_details,coin_username'],
            'topup_balance' => ['required', 'numeric']
        ]);
        if ($validation->fails()) {
            $response = [
                'status' => 400,
                'message' => $validation->errors()
            ];
            return $response;
        }
        $coin = new CoinRegisterController;
        $status = $coin->showByUsername($request->username_destination);
        if ($status->getStatusCode() != 200) {
            return $status;
        }
        $status = $status->getContent();
        $status = json_decode($status);
        $status = $status->data;
        $status = collect($status);
        $coinDetail = $status->first();

        $status = $this->showById($coinDetail->id);
        if ($status->getStatusCode() != 200) {
            return $status;
        }

        $status = $this->balanceCredit($coinDetail->id, $request->topup_balance);
        if ($status->getStatusCode() != 200) {
            return $status;
        }

        $transactions = [
            'coin_id_source' => $coinDetail->id,
            'coin_id_destination' => 0,
            'coin_transaction_code' => 1,
            'coin_transaction_type' => 0, //credit
            'coin_balance' => $request->topup_balance,
            'created_at' => date_format(now(), 'Y-m-d H:i:s'),
            'updated_at' => date_format(now(), 'Y-m-d H:i:s'),
        ];

        $request->request->add(['topup_transaction' => $transactions, 'coin_detail' => $coinDetail]);
        $coinTransaction = new CoinTransactionController;
        $status = $coinTransaction->storeTopUpTransaction($request);

        return $status;
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
