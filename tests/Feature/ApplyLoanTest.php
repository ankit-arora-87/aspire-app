<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\User;

class ApplyLoanTest extends TestCase
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
     * Apply loan test with all valid details.
     *
     * @return void
     */
    public function test_apply_loan_with_all_details()
    {
        $access_token = $this->getAccessToken();

        $proof_of_residence_upload_response = $this->json('POST', '/api/auth/documents/upload', [
            "document_type" => "PROOF_OF_RESIDENCE",
            "document" => UploadedFile::fake()->create('proof_of_residence.pdf', 50)
        ],
        [
            'Authorization' => 'Bearer '.$access_token
        ]);
        $proof_of_residence = $proof_of_residence_upload_response->getContent();
        $proof_of_residence_id = json_decode($proof_of_residence);

        $tax_salary_declaration_upload_response = $this->json('POST', '/api/auth/documents/upload', [
            "document_type" => "TAX_SALARY_DECLARATION",
            "document" => UploadedFile::fake()->create('tax_salary_declaration.pdf', 50)
        ],
        [
            'Authorization' => 'Bearer '.$access_token
        ]);
        $tax_salary_declaration = $tax_salary_declaration_upload_response->getContent();
        $tax_salary_declaration_id = json_decode($tax_salary_declaration);

        $response = $this->json('POST', '/api/auth/loans/apply', [
                "type" => "PERSONAL_LOAN",
                "requested_amount" => 100000,
                "duration" => 10,
                "documents" => [
                    [
                        "type" =>  "PROOF_OF_RESIDENCE",
                        "id" => $proof_of_residence_id->document_id
                    ],
                    [
                        "type" => "TAX_SALARY_DECLARATION",
                        "id" => $tax_salary_declaration_id->document_id
                    ]
                ]
            ],
            [
                'Authorization' => 'Bearer '.$access_token
            ]);
        $response->assertStatus(201);
    }

    /**
     * Apply loan test with invalid details.
     *
     * @return void
     */
    public function test_apply_loan_with_invalid_details()
    {
        $access_token = $this->getAccessToken();

        $response = $this->json('POST', '/api/auth/loans/apply', [
            "type" => "PERSONAL_LOAN",
            "requested_amount" => 10000,
            "duration" => 6,
            "documents" => [
                [
                    "type" =>  "PROOF_OF_RESIDENCE",
                    "id" => 4
                ],
                [
                    "type" => "TAX_SALARY_DECLARATION",
                    "id" => 5
                ]
            ],
            [
                'Authorization' => 'Bearer '.$access_token
            ]
        ]);
        $response->assertStatus(401);

    }

    /**
     * To get access token
     */
    public function getAccessToken(){

        $token_details = $this->json('POST', '/api/auth/login', [
            'email' => 'ankit+customer@gmail.com',
            'password' => 'Pass@2020',
            'remember_me' => true
            ], [
                'Accept' => 'application/json'
                ]
            );
        $token = $token_details->getContent();
        $access_token = json_decode($token);
        return $access_token->access_token;
    }
}
