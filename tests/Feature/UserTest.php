<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class UserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A user signup test with all valid details.
     *
     * @return void
     */
    public function testSignupWithAllDetails()
    {
        $response = $this->json('POST', '/api/auth/signup', [
                            'full_legal_name' => 'Ankit Customer',
                            'email' => 'ankit@aspire-cap.com',
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
    public function testSignupWithLimitedDetails()
    {
        $response = $this->json('POST', '/api/auth/signup', [
            
                'full_legal_name' => '',
                'email' => 'abcf@aspire-cap.com',
                'password' => '123',
                'role' => 'XYZ'
            ]);

        $response->assertStatus(422);

    }
}
