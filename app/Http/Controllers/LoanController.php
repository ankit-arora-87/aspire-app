<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\User;
use App\Loan;
use App\LoanDocuments;
use App\LoanLogs;
use App\LoanRepayments;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class LoanController extends Controller
{
    // To fetch loan type details
    public function getLoanTypes(){
        return response()->json(config('constants.loanTypes'));
    }

    // To create new loan for user
    public function createLoan(Request $request){
    $validation = Validator::make($request->all(),[
            'type' => [
                        'required',
                        'string',
                        Rule::in(
                            [
                                config('constants.loanTypes')[0]['type'],
                                config('constants.loanTypes')[1]['type']
                            ])
                            ],
            'requested_amount' => [
                        'required',
                        'numeric',
                        'min:100',
                        'max_if:type,'.config('constants.loanTypesLimit')[0]['PERSONAL']['amount'].','.config('constants.loanTypesLimit')[0]['BUSINESS']['amount'].''],
            'duration' => 'required|integer|min:1|max:12',
            'documents.*.type' => ['required', 'string',  Rule::in(
                [
                    config('constants.documentTypes')[0],
                    config('constants.documentTypes')[1]
                ])
                ],
            'documents.*.id' => ['required', 'integer', 'exists:documents,id,created_by,'.$request->user()->id.'']
        ]);

		if(!$validation->fails()){
            $applicationNo = Str::random();
			
			DB::beginTransaction();
                        try {
                            $loan = Loan::create([
                                'application_no' => $applicationNo,
                                'user_id' => $request->user()->id,
                                'type' => (($request->input('type') == config('constants.loanTypes')[0]['type']))?config('constants.loanTypes')[0]['type']:config('constants.loanTypes')[1]['type'],
                                'requested_amount' => $request->input('requested_amount'),
                                'duration' => $request->input('duration'),
                                'repayment_frequency' => 1, // monthly
                                'interest_rate' => (($request->input('type') == config('constants.loanTypes')[0]['type']))?config('constants.loanTypesLimit')[0]['PERSONAL']['interest_rate']:config('constants.loanTypesLimit')[0]['BUSINESS']['interest_rate'],
                                'status' => config('constants.loanStatus')['APPLIED'],
                                'created_by' => $request->user()->id,
                                'updated_by' => $request->user()->id
                            ]);
                            foreach ($request->input('documents') as $document){
                                $loanDocuments[] = new LoanDocuments([
                                    'document_id'  => $document['id'],
                                    'created_by' => $request->user()->id,
                                    'updated_by' => $request->user()->id
                                ]);
                            }
                            $loan->loanDocuments()->saveMany($loanDocuments);
                            $loan->loanLogs()->save(new LoanLogs([
                                    'action' => 'Loan applied',
                                    'created_by' => $request->user()->id
                            ]));
                            
                            DB::commit();
                            return response()->json([
                                'message' => 'Successfully loan applied!',
                                'application_no' => $applicationNo
                            ], 201);
                            // We can trigger emails/ push notifications to Manager (Loan Department)
                        } catch (\Exception $e) {
                            DB::rollback();
                            return response()->json(["message"=>"Loan is not applied yet. Please try again later!".$e->getMessage()], 422);
                        }

		}
		else{
			return response()->json($validation->errors(), 422);
		}
    }

    // To get customer specific loans
    public function getMyLoans(Request $request){

        return response()->json(
                Loan::select(
                    ['application_no', 'type', 'requested_amount', 'approved_amount', 'duration', 'repayment_frequency', 'interest_rate', 'status']
                )->where(['user_id' => $request->user()->id])->get()
            );
    }

    // To get loan detail
    public function getLoanDetail(Request $request){
        
        if($request->user()->hasRole(['Customer'])){
            $where = [
                'user_id' => $request->user()->id,
                'application_no' => $request->input('application_no')
            ];
        } else {
            $where = [
                'application_no' => $request->input('application_no')
            ];
        }
        $loanDetails =  Loan::with(['loanLogs','loanRepayments'])->where($where)->first();
        $userDetails = User::where(['id' => $loanDetails['user_id']])->select(['full_legal_name', 'email'])->first();
        $loanDetails['full_legal_name']= $userDetails['full_legal_name'];
        $loanDetails['email']= $userDetails['email'];
        return response()->json($loanDetails);
    }

    // To get all loans for Manager
    public function getAllLoans(Request $request){

        return response()->json(
                Loan::select(
                    ['loans.application_no', 'loans.type', 'loans.requested_amount', 'loans.approved_amount', 'loans.duration', 'loans.repayment_frequency', 'loans.interest_rate', 'loans.status', 'users.full_legal_name', 'users.email']
                )->join('users', 'users.id', '=', 'loans.user_id')->get()
            );
    }

    // To approve loan for user
    public function approveLoan(Request $request){
        $validation = Validator::make($request->all(),[
            'application_no' => 'required|string|max:100|exists:loans,application_no',
            'description' => 'max:500'
            ]);
    
            if(!$validation->fails()){
                $loan = Loan::where(['application_no' => $request->input('application_no')])->select(['id', 'status', 'requested_amount'])->first();  
                if(in_array($loan->status, [config('constants.loanStatus')['APPLIED'], config('constants.loanStatus')['INREVIEW']])){
                    $approvedAmount = round(.8*$loan->requested_amount); // this logic may vary
                    DB::beginTransaction();
                    try {
                        Loan::where(['id' => $loan->id])->update(
                            [
                                'status' => config('constants.loanStatus')['APPROVED'],
                                'approved_amount' => $approvedAmount 
                            ]);
                        LoanLogs::create([
                            'loan_id' => $loan->id,
                            'action' => config('constants.loanStatus')['APPROVED'],
                            'description' => !empty($request->input('description'))?$request->input('description'):'Loan - Approved',
                            'created_by' => $request->user()->id
                ]);
                        DB::commit();
                        return response()->json([
                            'message' => 'Successfully loan approved!',
                            'approved_amount' => $approvedAmount
                        ], 201);
                        // We can trigger emails/ push notifications to specific user

                    } catch (\Exception $e) {
                        DB::rollback();
                        return response()->json(["message"=>"Loan approval not done. Please try again later!".$e->getMessage()], 422);
                    }

                } else {
                    return response()->json(["message"=>"No approval is required for this loan application."], 422);    
                }
                
            }
            else{
                return response()->json($validation->errors(), 422);
            }
        }

        // To review loan for user
    public function reviewLoan(Request $request){
        $validation = Validator::make($request->all(),[
            'application_no' => 'required|string|max:100|exists:loans,application_no',
            'description' => 'required|string|max:500'
            ]);
    
            if(!$validation->fails()){
                $loan = Loan::where(['application_no' => $request->input('application_no')])->select(['id', 'status'])->first();  
                if(in_array($loan->status, [config('constants.loanStatus')['APPLIED'], config('constants.loanStatus')['INREVIEW']])){
                    DB::beginTransaction();
                    try {
                        Loan::where(['id' => $loan->id])->update(
                            [
                                'status' => config('constants.loanStatus')['DOCUMENTPENDING']
                            ]);
                        LoanLogs::create([
                            'loan_id' => $loan->id,
                            'action' => config('constants.loanStatus')['DOCUMENTPENDING'],
                            'description' => $request->input('description'),
                            'created_by' => $request->user()->id
                ]);
                        DB::commit();
                        return response()->json([
                            'message' => 'Successfully loan reviewed!'
                        ], 201);
                        // We can trigger emails/ push notifications to specific user
                    } catch (\Exception $e) {
                        DB::rollback();
                        return response()->json(["message"=>"Loan review not done. Please try again later!".$e->getMessage()], 422);
                    }

                } else {
                    return response()->json(["message"=>"No review is required for this loan application."], 422);    
                }
                
            }
            else{
                return response()->json($validation->errors(), 422);
            }
        }

    // To reject loan for user
    public function rejectLoan(Request $request){
        $validation = Validator::make($request->all(),[
            'application_no' => 'required|string|max:100|exists:loans,application_no',
            'description' => 'required|string|max:500'
            ]);
    
            if(!$validation->fails()){
                $loan = Loan::where(['application_no' => $request->input('application_no')])->select(['id', 'status'])->first();  
                if(in_array($loan->status, [config('constants.loanStatus')['APPLIED'], config('constants.loanStatus')['INREVIEW']])){
                    DB::beginTransaction();
                    try {
                        Loan::where(['id' => $loan->id])->update(
                            [
                                'status' => config('constants.loanStatus')['REJECTED']
                            ]);
                        LoanLogs::create([
                            'loan_id' => $loan->id,
                            'action' => config('constants.loanStatus')['REJECTED'],
                            'description' => $request->input('description'),
                            'created_by' => $request->user()->id
                ]);
                        DB::commit();
                        return response()->json([
                            'message' => 'Successfully loan rejected!'
                        ], 201);
                        // We can trigger emails/ push notifications to specific user
                    } catch (\Exception $e) {
                        DB::rollback();
                        return response()->json(["message"=>"Loan rejection not done. Please try again later!".$e->getMessage()], 422);
                    }

                } else {
                    return response()->json(["message"=>"No rejection is required for this loan application."], 422);    
                }
                
            }
            else{
                return response()->json($validation->errors(), 422);
            }
    }

    // To recort repayment for loan 
    public function recordRepayment(Request $request){
        $validation = Validator::make($request->all(),[
            'application_no' => 'required|string|max:100|exists:loans,application_no,created_by,'.$request->user()->id,
            'transaction_detail' => 'required|string|max:100|unique:loan_repayments,transaction_detail',
            'amount_paid' => 'required|numeric'
            ]);
    
            if(!$validation->fails()){
                $loan = Loan::where(['application_no' => $request->input('application_no')])->first();  
                if(in_array($loan->status, [config('constants.loanStatus')['APPROVED'], config('constants.loanStatus')['REPAYMENT']])){
                    $loanApprovedAmount = $loan->approved_amount;
                    $loanDuration = $loan->duration;
                    $loanInterestRate = $loan->interest_rate;
                    $paidRepayment = LoanRepayments::where(
                        [
                                'loan_id' => $loan->id, 
                                'type' => config('constants.repaymentType')['REPAYMENT']
                        ])->select(
                            [
                                DB::raw('count(*) as payment_count'), 
                                DB::raw('SUM(amount_paid) as paid_repayment')
                            ])->get();
                    $repaymentCount = $paidRepayment[0]->payment_count;
                    $repaymentAmount = $paidRepayment[0]->paid_repayment;
                    $loanInterestAmount = round(($loanApprovedAmount*$loanInterestRate)/(100));
                    $idealRepayment = round(($loanApprovedAmount + $loanInterestAmount)/$loanDuration);

                    if($repaymentCount > 0){
                        $loanApprovedAmount = ($loanApprovedAmount + $loanInterestAmount - $repaymentAmount);
                        $idealRepayment = round(($loanApprovedAmount)/($loanDuration - $repaymentCount));
                    } 
                    if(($request->input('amount_paid') < ($idealRepayment - 5)) || ($request->input('amount_paid') > ($loanApprovedAmount + 5))){
                        return response()->json(["message"=>"Repayment amount should lie between ".$idealRepayment." - ".$loanApprovedAmount."."], 422); 
                    }
                    $differenceAmount = ($loanApprovedAmount - $request->input('amount_paid'));
                    if(($differenceAmount >= -5) && ($differenceAmount <= 5)){
                        $loanStatus = config('constants.loanStatus')['PAID'];
                    } else {
                        $loanStatus = config('constants.loanStatus')['REPAYMENT'];
                    }
                    DB::beginTransaction();
                    try {
                        Loan::where(['id' => $loan->id])->update([
                            'status' => $loanStatus
                        ]);
                        LoanLogs::create([
                            'loan_id' => $loan->id,
                            'action' => $loanStatus,
                            'created_by' => $request->user()->id
                        ]);
                        LoanRepayments::create([
                            'loan_id' => $loan->id,
                            'type' => config('constants.repaymentType')['REPAYMENT'],
                            'description' => $loanStatus,
                            'amount_paid' => $request->input('amount_paid'),
                            'payment_month' => 1,
                            'transaction_detail' => $request->input('transaction_detail'),
                            'created_by' => $request->user()->id
                        ]);
                        DB::commit();
                        return response()->json([
                            'message' => 'Successfully loan repayment done!'
                        ], 201);
                        // We can trigger emails/ push notifications to specific user
                    } catch (\Exception $e) {
                        DB::rollback();
                        return response()->json(["message"=>"Loan repayment not done. Please try again later!".$e->getMessage()], 422);
                    }
                   
                } else {
                    return response()->json(["message"=>"No loan repayment is required for this loan application."], 422);    
                }
                
            }
            else{
                return response()->json($validation->errors(), 422);
            }
    }

}
