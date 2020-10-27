<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class CustomerLoginTest extends TestCase
{
    use WithFaker, RefreshDatabase;    

    public function setUp():void
    {
        parent::setUp();
        $this->artisan('db:seed',['--class' => 'RoleSeeder', '--env' => 'testing']);
        $this->artisan('db:seed',['--class' => 'CustomerSeeder', '--env' => 'testing']);
        $this->artisan('passport:install');
    }
    /**
     * A user login test with all valid details.
     *
     * @return void
     */
    public function test_login_with_all_valid_details()
    {
        $response = $this->json('POST', '/api/auth/login', [
                'email' => 'ankit+customer@gmail.com',
                'password' => 'Pass@2020',
                'remember_me' => true
        ], [
            'Accept' => 'application/json'
            ]
        );

        $response->assertJsonStructure([
            'access_token', 'token_type', 'expires_at'
        ]);
        $response->assertStatus(200);

    }
    /**
     * A user login test with invalid details.
     *
     * @return void
     */
    public function test_login_with_all_invalid_details()
    {
        $response = $this->json('POST', '/api/auth/login', [
            'email' => 'ankit+customer1@gmail.com',
            'password' => 'Pass@2020',
            'remember_me' => false
        ]);

        $response->assertExactJson([
            "message" => "Unauthorized"                
        ]);
        $response->assertStatus(401);    
    }    
}
