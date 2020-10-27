<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\User;
use App\Loan;

class ProcessLoanTest extends TestCase
{
    use WithFaker, RefreshDatabase;    

    public function setUp():void
    {
        parent::setUp();
        $this->artisan('db:seed',['--class' => 'RoleSeeder', '--env' => 'testing']);
        $this->artisan('db:seed',['--class' => 'CustomerSeeder', '--env' => 'testing']);
        $this->artisan('db:seed',['--class' => 'ManagerSeeder', '--env' => 'testing']);
        $this->artisan('db:seed',['--class' => 'LoanSeeder', '--env' => 'testing']);
        $this->artisan('passport:install');        
        $this->artisan('passport:keys');               
    }
   
    /**
     * Review loan test with invalid details.
     *
     * @return void
     */
    public function test_review_loan_with_invalid_details()
    {
        $application_details = Loan::where(['status' => 'Applied'])->first();
        $m_access_token = $this->getAccessToken('Manager');

        $response = $this->json('POST', '/api/auth/loans/review', [
            "application_no" =>  isset($application_details->application_no)?$application_details->application_no:'Random',
            "description" => "Please upload good quality documents!"
            ],
            [
                'Authorization' => 'Bearer '.$m_access_token,
                'Content-Type' => 'application/json'
            ]
        );
        // $response->dump();
        $response->assertStatus(422);

    }

    /**
     * Approve loan test with invalid details.
     *
     * @return void
     */
    public function test_approve_loan_with_invalid_details()
    {
        $application_details = Loan::where(['status' => 'Applied'])->first();
        $m_access_token = $this->getAccessToken('Manager');

        $response = $this->json('POST', '/api/auth/loans/approve', [
            "application_no" =>  isset($application_details->application_no)?$application_details->application_no:'Random',
            "description" => "Please upload good quality documents!"
            ],
            [
                'Authorization' => 'Bearer '.$m_access_token,
                'Content-Type' => 'application/json'
            ]
        );
        // $response->dump();
        $response->assertStatus(422);

    }

    /**
     * Reject loan test with invalid details.
     *
     * @return void
     */
    public function test_reject_loan_with_invalid_details()
    {
        $application_details = Loan::where(['status' => 'Applied'])->first();
        $m_access_token = $this->getAccessToken('Manager');
        $response = $this->json('POST', '/api/auth/loans/reject', [
            "application_no" =>  isset($application_details->application_no)?$application_details->application_no:'Random',
            "description" => "Please upload good quality documents!"
            ],
            [
                'Authorization' => 'Bearer '.$m_access_token,
                'Content-Type' => 'application/json'
            ]
        );
        // $response->dump();
        $response->assertStatus(422);

    }

    /**
     * To get access token
     */
    public function getAccessToken($userType){

        $email = (($userType == 'Manager')? 'ankit+manager@gmail.com' : 'ankit+customer@gmail.com');
        $token_details = $this->json('POST', '/api/auth/login', [
            'email' => $email,
            'password' => 'Pass@2020',
            'remember_me' => false
            ], [
                'Accept' => 'application/json'
                ]
            );
            // $token_details->dump();
        $token = $token_details->getContent();
        $access_token_details = json_decode($token);
        return $access_token_details->access_token;
    }

}
