<?php

namespace App\Http\Controllers\Coin;

use App\Http\Controllers\Controller;

use App\Model\CoinDetail as coin_details;
use App\Model\CoinLogin as coin_logins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

class CoinRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coinData = [];
        $coinDetails = coin_details::all();
        foreach ($coinDetails as $coin => $detail) {
            $coinData[] = [
                'id' => $detail->id,
                'username' => $detail->coin_username,
                'fullname' => $detail->coin_fullname,
                'dob' => $detail->coin_dob,
                'address' => $detail->coin_address,
                'sex' => $detail->coin_sex == 0 ? 'female' : 'male',
                'email' => $detail->coin_email,
                'phone' => $detail->coin_phone,
                'coin_account_type' => $detail->coin_account_type,
                'join_date' => date_format($detail->created_at, 'Y-m-d H:i:s')
            ];
        }
        $response = [
            'status' => 200,
            'data' => $coinData
        ];
        return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        // input validation
        $validation = Validator::make($request->all(), [
            'username' => ['required', 'unique:dbmarketcoins.coin_logins,coin_username'],
            'password' => ['required', 'min:8', 'max:12', 'regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*(),.?":{}|<>])(?=.*[\d]).{8,12}$/'],
            'email' => ['required', 'email', 'unique:dbmarketcoins.coin_details,coin_email'],
            'account_type' => ['required', 'numeric'],
            'coin_dob' => ['date'],
            'coin_sex' => ['boolean'],
            'coin_phone' => ['numeric'],
        ]);
        if ($validation->fails()) {
            $response = [
                'status' => 401,
                'message' => $validation->errors()
            ];
            return response()->json($response, 401);
        }

        $coinDetail = [
            'coin_fullname' => $request->input('fullname') ?: $request->input('username'),
            'coin_dob' => $request->input('dob'),
            'coin_address' => $request->input('address'),
            'coin_sex' => $request->input('sex'),
            'coin_email' => $request->input('email'),
            'coin_phone' => $request->input('phone'),
            'coin_username' => $request->input('username'),
            'coin_account_type' => $request->input('account_type'),
            'created_at' => date_format(now(), 'Y-m-d H:i:s'),
            'updated_at' => date_format(now(), 'Y-m-d H:i:s')
        ];
        $coinID = coin_details::insertGetId($coinDetail);
        $coinLogin = [
            'coin_username' => $request->input('username'),
            'coin_password' => Hash::make($request->input('password')),
            'coin_status' => 1,
            'coin_id' => $coinID,
            'created_at' => date_format(now(), 'Y-m-d H:i:s'),
            'updated_at' => date_format(now(), 'Y-m-d H:i:s')
        ];
        $status = coin_logins::insert($coinLogin);

        if (!$status) {
            $response = [
                'status' => 500,
                'message' => 'Internal Server Error'
            ];
            return response()->json($response, 500);
        }

        // create account balance
        $coinLogin = collect($coinLogin);
        $coinLogin = $coinLogin->except(['coin_status', 'coin_password']);
        $request->request->add(['coin_balance' => $coinLogin]);
        $coinBalance = new CoinBalanceController;
        $status = $coinBalance->store($request);
        if ($status->getStatusCode() != 200) {
            return $status;
        }

        $response = [
            'status' => 200,
            'coin_username' => $coinLogin['coin_username'],
            'token' => coin_details::find($coinID)->createToken('register_token', ['coin'])->accessToken
        ];
        return response()->json($response, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CoinRegister  $coinRegister
     * @return \Illuminate\Http\Response
     */
    public function show(...$coinID)
    {
        $coinID = collect($coinID);
        $coinID = $coinID->flatten();
        $coinDetails = coin_details::whereIn('id', $coinID)->get();
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
                'username' => $item->coin_username,
                'fullname' => $item->coin_fullname,
                'dob' => $item->coin_dob,
                'address' => $item->coin_address,
                'sex' => $item->coin_sex == 0 ? 'female' : 'male',
                'email' => $item->coin_email,
                'phone' => $item->coin_phone,
                'join_date' => date_format($item->created_at, 'Y-m-d H:i:s')
            ];
            return $item;
        });
        $response = [
            'status' => 200,
            'data' => $coinDetails
        ];
        return response()->json($response, 200);
    }

    public function showByUsername(...$coinUsername)
    {
        $coinUsername = collect($coinUsername);
        $coinUsername = $coinUsername->flatten();
        $coinDetails = coin_details::whereIn('coin_username', $coinUsername)->get();
        $coinUsernameCheck = $coinDetails->map(function ($item) {
            return $item->coin_username;
        });
        $status = $coinUsername->diff($coinUsernameCheck);
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
                'username' => $item->coin_username,
                'fullname' => $item->coin_fullname,
                'dob' => $item->coin_dob,
                'address' => $item->coin_address,
                'sex' => $item->coin_sex == 0 ? 'female' : 'male',
                'email' => $item->coin_email,
                'phone' => $item->coin_phone,
                'accoun_type' => $item->coin_account_type,
                'join_date' => date_format($item->created_at, 'Y-m-d H:i:s')
            ];
            return $item;
        });
        $response = [
            'status' => 200,
            'data' => $coinDetails
        ];
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CoinRegister  $coinRegister
     * @return \Illuminate\Http\Response
     */
    public function edit(CoinRegister $coinRegister)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CoinRegister  $coinRegister
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CoinRegister $coinRegister)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CoinRegister  $coinRegister
     * @return \Illuminate\Http\Response
     */
    public function destroy(CoinRegister $coinRegister)
    {
        //
    }
}
