<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as Role;
Use App\User;
Use App\Loan;
use App\LoanLogs;

class LoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $applicationNo = Str::random();
        $user = User::where(['email' => 'ankit+customer@gmail.com'])->first();
        DB::beginTransaction();
            try {
                $loan = Loan::create([
                    'application_no' => $applicationNo,
                    'user_id' => $user->id,
                    'type' => config('constants.loanTypes')[0]['type'],
                    'requested_amount' => 10000,
                    'duration' => 12,
                    'repayment_frequency' => 1, // monthly
                    'interest_rate' => config('constants.loanTypesLimit')[0]['PERSONAL']['interest_rate'],
                    'status' => config('constants.loanStatus')['APPLIED'],
                    'created_by' => $user->id,
                    'updated_by' => $user->id
                ]);

                $loan->loanDocuments()->saveMany($loanDocuments);
                $loan->loanLogs()->save(new LoanLogs([
                        'action' => 'Loan applied',
                        'created_by' => $user->id,
                ]));
                
                DB::commit();               
            } catch (\Exception $e) {
                DB::rollback();
            }          
    }
}
