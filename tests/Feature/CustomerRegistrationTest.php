<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class CustomerRegistrationTest extends TestCase
{
    use WithFaker, RefreshDatabase;    

    public function setUp():void
    {
        parent::setUp();
        $this->artisan('db:seed',['--class' => 'RoleSeeder', '--env' => 'testing']);
        $this->artisan('db:seed',['--class' => 'ManagerSeeder', '--env' => 'testing']);
    }
    /**
     * A user signup test with all valid details.
     *
     * @return void
     */
    public function test_signup_with_all_details()
    {
        $faker = $this->faker; 
        $response = $this->json('POST', '/api/auth/signup', [
                            'full_legal_name' => $faker->firstName(). ' '.$faker->lastName(),
                            'email' => $faker->unique()->email,
                            'password' => 'Password@12345',
                            'role' => 'Manager'
            ]);

        $response->assertStatus(201);

    }
    /**
     * A user signup test with limited details.
     *
     * @return void
     */
    public function test_signup_with_missing_required_field()
    {
        $faker = $this->faker; 
        $response = $this->json('POST', '/api/auth/signup', [
            
                'full_legal_name' => '',
                'email' => $faker->unique()->email,
                'password' => 'Password@12345',
                'role' => 'Customer'
            ]);

        $response->assertExactJson([
            "full_legal_name" => [
                "The full legal name field is required."
                ]
        ]);
        $response->assertStatus(422);

    }
    /**
     * A user signup test with existing email.
     *
     * @return void
     */
    public function test_signup_with_existing_email()
    {
        $faker = $this->faker; 
        $email = $faker->unique()->email;
        // Firstly create user with $email
        $this->json('POST', '/api/auth/signup', [
                'full_legal_name' => $faker->firstName() .' '. $faker->lastName(),
                'email' => $email,
                'password' => 'Password@12345',
                'role' => 'Customer'
            ]);

        // Secondly try to create user with same $email again
        $response = $this->json('POST', '/api/auth/signup', [
                'full_legal_name' => $faker->firstName() .' '. $faker->lastName(),
                'email' => $email,
                'password' => 'Password@12345',
                'role' => 'Customer'
            ]);

        $response->assertExactJson([
            "email" => [
                "The email has already been taken."
                ]
        ]);
        $response->assertStatus(422);

    }
}
