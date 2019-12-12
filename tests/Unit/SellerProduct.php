<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class SellerProduct extends TestCase
{
    public function login()
    {
        /*
        seller must have logged in
        */

        $URL = 'http://127.0.0.1:8000/api/seller/3/profile';
        $response = $this->get($URL);
        $user = $response->decodeResponseJson();
        $user = $user['data'][0];

        // seller login
        $URL = 'http://127.0.0.1:8000/api/seller/login';
        $input = [
            'username' => $user['username'],
            'password' => 'SlTest77#'
        ];
        $response = $this->post($URL,$input);
        $token = $response->decodeResponseJson();
        $token = $token['token'];
        return $token;
           }
    /**
     * A basic unit test example.
     *
     * @return void
     */
    /** @test */
    public function sellerAddProduct()
    {
        $this->token = $this->login();
        $input = [
            'product_name' => Str::random(20),
            'product_price' => rand(1, 99999),
            'product_stock' =>  rand(1, 100),
            'product_image' => UploadedFile::fake()->image('fakerImage.jpg')->size(100)
        ];
        $headers = [
            'Authorization' => 'Bearer '.$this->token
        ];
        $response = $this->post('/api/seller/product', $input,$headers);
        $response->assertStatus(200);
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    /** @test */
    public function sellerUpdateProduct()
    {

        $this->token = $this->login();
        // get shop info
        $URL = 'http://127.0.0.1:8000/api/seller';
        $headers = [
            'Authorization' => 'Bearer '.$this->token
        ];
        $response = $this->get($URL,$headers);
        $response = $response->decodeResponseJson();
        $shop = $response['data'];
        //

        // get all product in shop
        $URL = 'http://127.0.0.1:8000/api/seller/'.$shop['id'].'/product/';
        $response = $this->get($URL);
        $response = $response->decodeResponseJson();
        $product = $response['data'];
        //

        $input = [
            'id' => $product[0]['id'],
            'product_name' => Str::random(20),
            'product_price' => rand(1, 99999),
            'product_stock' =>  rand(1, 100),
            'product_image' => UploadedFile::fake()->image('fakerImage.jpg')->size(100),
            '_method' => 'PUT'
        ];
        $headers = [
            'Authorization' => 'Bearer '.$this->token
        ];
        $response = $this->post('/api/seller/product', $input,$headers);
        $response->assertStatus(200);
    }

        /**
     * A basic unit test example.
     *
     * @return void
     */
    /** @test */
    public function sellerDeleteProduct()
    {

        $this->token = $this->login();
        // get shop info
        $URL = 'http://127.0.0.1:8000/api/seller';
        $headers = [
            'Authorization' => 'Bearer '.$this->token
        ];
        $response = $this->get($URL,$headers);
        $response = $response->decodeResponseJson();
        $shop = $response['data'];
        //

        // get all product in shop
        $URL = 'http://127.0.0.1:8000/api/seller/'.$shop['id'].'/product/';
        $response = $this->get($URL);
        $response = $response->decodeResponseJson();
        $product = $response['data'];
        //

        $input = [
            'id' => $product[0]['id'],
            '_method'=> 'DELETE'
        ];
        $headers = [
            'Authorization' => 'Bearer '.$this->token
        ];
        $response = $this->post('/api/seller/product', $input,$headers);
        $response->assertStatus(200);
    }
}
