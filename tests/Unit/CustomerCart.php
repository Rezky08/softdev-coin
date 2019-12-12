<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerFeature extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function customerAddToCart()
    {
        /*
        customer must have logged in
        */

        $URL = 'http://127.0.0.1:8000/api/customer/3/profile';
        $response = $this->get($URL);
        $user = $response->decodeResponseJson();
        $user = $user['data'][0];

        // customer login
        $URL = 'http://127.0.0.1:8000/api/customer/login';
        $input = [
            'username' => $user['username'],
            'password' => 'CsTest77#'
        ];
        $response = $this->post($URL,$input);
        $token = $response->decodeResponseJson();
        $token = $token['token'];
        //

        $URL = 'http://127.0.0.1:8000/api/customer/cart';
        $input = [
            'product_id' => 1,
            'qty' => 1
        ];
        $headers =[
            'Authorization' => 'Bearer '.$token
        ];
        $response = $this->post($URL,$input,$headers);
        $response->assertStatus(200);
    }
}
