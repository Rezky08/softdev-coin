<?php

namespace Tests\Unit;

use App\Model\SellerProduct as seller_products;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\Cast\Int_;
use Tests\TestCase;

class sellerTest extends TestCase
{

    /**
     * A basic unit test example.
     *
     * @return void
     */
    /** @test */
    public function sellerCanRegister()
    {
        $input = [
            'username' => 'iniusername' . Str::random(3),
            'fullname' => 'ini nama ' . Str::random(4),
            'email' => 'ini_email' . Str::random(4) . '@gmail.com',
            'password' => 'P@sswr0d!',
            'sex' => 0
        ];
        $response = $this->post('/api/seller/register', $input);
        $response->assertStatus(200);
    }

    public function sellerCanLogin()
    {
        $input = [
            'username' => 'rezky221190',
            'password' => 'Test123#'
        ];
        $response = $this->post('/api/seller/login', $input);
        $this->token = "Bearer " . $response->decodeResponseJson()['token'];
        $response->assertStatus(200);
        return $this->token;
    }

    public function selllerGetInfo($token)
    {
        $this->token = $token;
        $this->withHeaders(['Authorization' => $this->token]);
        $response = $this->get('/api/seller');
        return $response->decodeResponseJson();
    }
    public function sellerCanAddTheirProduct($token)
    {
        $this->token = $token;
        $this->withHeaders(['Authorization' => $this->token]);
        $input = [
            'product_name' => Str::random(20),
            'product_price' => rand(1, 99999),
            'product_stock' =>  rand(1, 100),
            'product_image' => UploadedFile::fake()->image('fakerImage.jpg')->size(100)
        ];
        $response = $this->post('/api/seller/product', $input);
        $response->assertStatus(200);
    }


    public function sellerCanUpdateTheirProduct($token, $id)
    {
        $this->token = $token;
        $this->withHeaders(['Authorization' => $this->token]);
        $input = [
            'id' => $id,
            'product_name' => Str::random(20),
            'product_price' => rand(1, 99999),
            'product_stock' =>  rand(1, 100),
            'product_image' => UploadedFile::fake()->image('fakerImage.jpg')->size(100),
            '_method' => 'PUT'
        ];
        $response = $this->post('/api/seller/product', $input);
        $response->assertStatus(200);
    }
    public function sellerCanDeleteTheirProduct($token, $id)
    {
        $this->token = $token;
        $this->withHeaders(['Authorization' => $this->token]);
        $input = [
            'id' => $id,
            '_method' => "DELETE"
        ];
        $response = $this->post('/api/seller/product', $input);
        $response->assertStatus(200);
    }
    /** @test */
    public function sellerAction()
    {
        $this->token = $this->sellerCanLogin();
        $this->sellerShop = $this->selllerGetInfo($this->token)['data']['shop'];
        $this->sellerCanAddTheirProduct($this->token);
        $this->id = seller_products::where('seller_shop_id', $this->sellerShop['id'])->first()->id;
        $this->sellerCanUpdateTheirProduct($this->token, $this->id);
        $this->sellerCanDeleteTheirProduct($this->token, $this->id);
    }
}
