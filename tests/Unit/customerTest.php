<?php

namespace Tests\Unit;

use App\Model\CustomerCart as customer_carts;
use App\Model\SellerProduct as seller_products;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class customerTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function customerCanRegister()
    {
        $input = [
            'username' => 'iniusername' . Str::random(3),
            'fullname' => 'ini nama ' . Str::random(4),
            'email' => 'iniEmail' . Str::random(4) . '@gmail.com',
            'password' => 'P@sswr0d!',
            'sex' => 0
        ];
        $response = $this->post('/api/customer/register', $input);
        $response->assertStatus(200);
        return $input;
    }

    public function customerProfile($token)
    {
        $this->token = $token;
        $this->withHeaders(['Authorization' => $this->token]);
        $response = $this->get('/api/customer');
        $response->assertStatus(200);
        return $response->decodeResponseJson();
    }

    public function customerCanLogin($input)
    {
        $response = $this->post('/api/customer/login', $input);
        $this->token = "Bearer " . $response->decodeResponseJson()['token'];
        $response->assertStatus(200);
        return $this->token;
    }

    public function customerCanAddToCart($token, $productId)
    {
        $this->token = $token;
        $this->withHeaders(['Authorization' => $this->token]);
        $input = [
            'product_id' => $productId,
            'qty' => 2,
        ];
        $response = $this->post('/api/customer/cart', $input);
        $response->assertStatus(200);
    }
    public function customerCanUpdateCart($token, $productId)
    {
        $this->token = $token;
        $this->withHeaders(['Authorization' => $this->token]);
        $input = [
            'product_id' => $productId,
            'qty' => 2,
        ];
        $response = $this->post('/api/customer/cart', $input);
        $response->assertStatus(200);
    }
    public function customerCanRemoveCart($token, $cartId)
    {
        $this->token = $token;
        $this->withHeaders(['Authorization' => $this->token]);
        $input = [
            'id' => $cartId,
            '_method' => 'DELETE'
        ];
        $response = $this->post('/api/customer/cart', $input);
        $response->assertStatus(200);
    }

    /** @test */
    public function customerAction()
    {
        $productId = seller_products::all()->first()->id;
        $input = $this->customerCanRegister();
        $input = [
            'username' => $input['username'],
            'password' => $input['password']
        ];
        $this->token = $this->customerCanLogin($input);
        $this->customerCanAddToCart($this->token, $productId);
        $customerAcc = $this->customerProfile($this->token)['data'];
        $cartId = customer_carts::where('customer_id', $customerAcc['id'])->orderBy('created_at', 'desc')->first()->id;
        $this->customerCanUpdateCart($this->token, $cartId);
        $this->customerCanRemoveCart($this->token, $cartId);
    }
}
