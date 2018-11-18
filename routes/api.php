<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/
Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
  
    Route::group([
      'middleware' => ['auth:api','role:Manager']
    ], function() {
        Route::get('loans/list', 'LoanController@getAllLoans') ;
        Route::post('loans/approve', 'LoanController@approveLoan') ;
        Route::post('loans/reject', 'LoanController@rejectLoan') ;
        Route::post('loans/review', 'LoanController@reviewLoan') ;
    });
    Route::group([
        'middleware' => ['auth:api','role:Customer']
      ], function() {
          Route::get('loans/types', 'LoanController@getLoanTypes') ;
          Route::post('loans/apply', 'LoanController@createLoan') ;
          Route::post('documents/upload', 'DocumentController@uploadDocument') ;
          Route::get('loans', 'LoanController@getMyLoans') ;
          Route::post('documents/upload-review', 'DocumentController@uploadReviewDocument') ;
          Route::post('loans/repayment', 'LoanController@recordRepayment') ;
      });
      Route::group([
        'middleware' => ['auth:api','role:Manager|Customer']
      ], function() {
          Route::get('logout', 'AuthController@logout');
          Route::get('user', 'AuthController@user');
          Route::get('loans/detail', 'LoanController@getLoanDetail') ;
      });
});
