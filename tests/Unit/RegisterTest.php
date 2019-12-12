<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    /** @test */
    public function customerRegister()
    {
        /*
            input :
                username : ['required','unique']
                password : ['required','contain Uppercase,lowercase,special char,min8,max12']
                sex : ['required','0 female, 1 male']
                email : ['required','unique','must email']
         */
        $URL = 'http://127.0.0.1:8000/api/customer/register';
        $input =[
            'username' => 'customerTest'.Str::random(5),
            'password' => 'CsTest77#',
            'sex' => random_int(0,1),
            'email'=> 'customerTest'.Str::random(5).'@ugm.ac.id'
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
    public function sellerRegister()
    {
        /*
            input :
                username : ['required','unique']
                password : ['required','contain Uppercase,lowercase,special char,min8,max12']
                sex : ['required','0 female, 1 male']
                email : ['required','unique','must email']
         */
        $URL = 'http://127.0.0.1:8000/api/seller/register';
        $input =[
            'username' => 'sellerTest'.Str::random(5),
            'password' => 'SlTest77#',
            'sex' => random_int(0,1),
            'email'=> 'sellerTest'.Str::random(5).'@ugm.ac.id'
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
    public function supplierRegister()
    {
        /*
            input :
                username : ['required','unique']
                password : ['required','contain Uppercase,lowercase,special char,min8,max12']
                sex : ['required','0 female, 1 male']
                email : ['required','unique','must email']
         */
        $URL = 'http://127.0.0.1:8000/api/supplier/register';
        $input =[
            'username' => 'supplierTest'.Str::random(5),
            'password' => 'SpTest77#',
            'sex' => random_int(0,1),
            'email'=> 'supplierTest'.Str::random(5).'@ugm.ac.id'
        ];
        $response = $this->post($URL,$input);
        $response->assertStatus(200);
    }
}
