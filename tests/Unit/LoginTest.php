<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    /** @test */
    public function customerLogin()
    {
        /*
            input :
                username : ['required','unique']
                password : ['required','contain Uppercase,lowercase,special char,min8,max12']
            get 1 user from database
         */
        $URL = 'http://127.0.0.1:8000/api/customer/3/profile';
        $response = $this->get($URL);
        $user = $response->decodeResponseJson();
        $user = $user['data'][0];
        $URL = 'http://127.0.0.1:8000/api/customer/login';
        $input =[
            'username' => $user['username'],
            'password' => 'CsTest77#'
        ];
        $response = $this->post($URL,$input);
        $response->assertStatus(200);
    }

        /**
     * A basic unit test example.
     *
     * @return void
     */
    /** @test */
    public function sellerLogin()
    {
        /*
            input :
                username : ['required','unique']
                password : ['required','contain Uppercase,lowercase,special char,min8,max12']
            get 1 user from database
         */
        $URL = 'http://127.0.0.1:8000/api/seller/3/profile';
        $response = $this->get($URL);
        $user = $response->decodeResponseJson();
        $user = $user['data'][0];
        $URL = 'http://127.0.0.1:8000/api/seller/login';
        $input =[
            'username' => $user['username'],
            'password' => 'SlTest77#'
        ];
        $response = $this->post($URL,$input);
        $response->assertStatus(200);
    }

            /**
     * A basic unit test example.
     *
     * @return void
     */
    /** @test */
    public function supplierLogin()
    {
        /*
            input :
                username : ['required','unique']
                password : ['required','contain Uppercase,lowercase,special char,min8,max12']
            get 1 user from database
         */
        $URL = 'http://127.0.0.1:8000/api/supplier/3/profile';
        $response = $this->get($URL);
        $user = $response->decodeResponseJson();
        $user = $user['data'][0];
        $URL = 'http://127.0.0.1:8000/api/supplier/login';
        $input =[
            'username' => $user['username'],
            'password' => 'SpTest77#'
        ];
        $response = $this->post($URL,$input);
        $response->assertStatus(200);
    }
}
