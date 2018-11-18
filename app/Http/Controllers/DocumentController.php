<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\User;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use App\Document;
use App\LoanDocuments;
use App\Loan;
use App\LoanLogs;

class DocumentController extends Controller
{
    // To upload documents
    public function uploadDocument(Request $request) {
        $validation = Validator::make($request->only(['document_type', 'document']), [
                    'document_type' => ['required','string', Rule::in(
                        [
                            config('constants.documentTypes')[0],
                            config('constants.documentTypes')[1]
                        ])],
                    'document' => 'required|file|mimes:jpeg,jpg,png,bmp,pdf,doc,docx'
        ]);
        if (!$validation->fails()) {    
            if($request->file('document')){
                $uploadedFilePath = CommonHelper::uploadDocument($request->file('document'), config('constants.loans_dir'));
                $document = $uploadedFilePath;
            }
            DB::beginTransaction();
            try {
                $documentUploaded = Document::create([
                    'name' => $request->file('document')->getClientOriginalName(),
                    'description' => $request->input('document_type'),
                    'alias' => $document,
                    'path' => config('constants.loans_dir'),
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id
                ]);
                DB::commit();
                return response()->json([
                    'message' => 'Successfully document uploaded!',
                    'document_id' => $documentUploaded->id
				], 201);
            } catch (\Exception $e) {
                if(isset($uploadedFilePath)){
                    @unlink($uploadedFilePath);
                }
                DB::rollback();
                return response()->json(["message"=>"Document is not uploaded yet. Please try again later!".$e->getMessage()], 422);

            }
        }
        else {
            return response()->json($validation->errors(), 422);
        }
    }

    // To upload review documents
    public function uploadReviewDocument(Request $request) {
        $validation = Validator::make($request->only(['document_type', 'document', 'application_no']), [
                    'application_no' => 'required|string|max:100|exists:loans,application_no,created_by,'.$request->user()->id.'',
                    'document_type' => ['required','string', Rule::in(
                        [
                            config('constants.documentTypes')[0],
                            config('constants.documentTypes')[1]
                        ])],
                    'document' => 'required|file|mimes:jpeg,jpg,png,bmp,pdf,doc,docx'
        ]);
        if (!$validation->fails()) {    
            
            $loan = Loan::where(['application_no' => $request->input('application_no')])->first();   
            if(in_array($loan->status, [config('constants.loanStatus')['DOCUMENTPENDING']])){
                if($request->file('document')){
                    $uploadedFilePath = CommonHelper::uploadDocument($request->file('document'), config('constants.loans_dir'));
                    $document = $uploadedFilePath;
                }
                DB::beginTransaction();
                try {
                    $documentUploaded = Document::create([
                    'name' => $request->file('document')->getClientOriginalName(),
                    'description' => $request->input('document_type'),
                    'alias' => $document,
                    'path' => config('constants.loans_dir'),
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id
                    ]);
                    LoanDocuments::create([
                        'loan_id' => $loan->id,
                        'document_id' => $documentUploaded->id,
                        'created_by' => $request->user()->id,
                        'updated_by' => $request->user()->id

                    ]);    

                    Loan::where(['id' => $loan->id])->update(['status' => config('constants.loanStatus')['INREVIEW']]);
                    LoanLogs::create([
                        'loan_id' => $loan->id,
                        'action' => config('constants.loanStatus')['INREVIEW'],
                        'description' => 'Loan - Review dcoument uploaded',
                        'created_by' => $request->user()->id
                    ]);
                    DB::commit();
                    return response()->json([
                        'message' => 'Successfully review document uploaded!'
                    ], 201);
                } catch (\Exception $e) {
                    if(isset($uploadedFilePath)){
                        @unlink($uploadedFilePath);
                    }
                    DB::rollback();
                    return response()->json(["message"=>"Document is not uploaded yet. Please try again later!".$e->getMessage()], 422);
                }
            }
            else {
                return response()->json(["message"=>"No review document is required for this loan application."], 422);    
            }
        }
        else {
            return response()->json($validation->errors(), 422);
        }
    }
}
