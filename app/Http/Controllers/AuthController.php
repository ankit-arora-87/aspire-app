<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\User;
use Validator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as Role;
use Spatie\Permission\Models\Permission as Permission;

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] full_legal_name
     * @param  [string] email
     * @param  [string] password
     * @return [string] message
     */
    public function signup(Request $request)
    {
		
		$validation = Validator::make($request->all(),[
            'full_legal_name' => 'required|string|max:100',
            'email' => 'required|string|email|unique:users|max:190',
            'password' => 'required|string|min:6|max:50',
            'role' => ['required',  Rule::in(
                ['Manager', 'Customer'])]
        ]);
		if(!$validation->fails()){
            DB::beginTransaction();
            try {
                    $user = new User([
                    'full_legal_name' => $request->full_legal_name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ]);
                    $user->save();
                    $user->assignRole($request->input('role')); //: Assigns role to a user
                    DB::commit();                                
                    return response()->json([
                        'message' => 'Successfully created user!'
                    ], 201);

                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json([
                        'message' => 'Something went wrong, please try again after sometime!'
                    ], 400);
                }

		}
		else{
			return response()->json($validation->errors(), 422);
		}
        
    }
  
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        
		$validation = Validator::make($request->all(),[
			'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
		if(!$validation->fails()){
			$credentials = request(['email', 'password']);
			if(!Auth::attempt($credentials)){
				return response()->json([
					'message' => 'Unauthorized'
				], 401);
			}
			$user = $request->user();
			$tokenResult = $user->createToken('Personal Access Token');
			$token = $tokenResult->token;
			if ($request->remember_me){
				$token->expires_at = Carbon::now()->addWeeks(1);
			}
			$token->save();
			return response()->json([
				'access_token' => $tokenResult->accessToken,
				'token_type' => 'Bearer',
				'expires_at' => Carbon::parse(
					$tokenResult->token->expires_at
				)->toDateTimeString()
			]);
		}
		else{
			return response()->json($validation->errors(), 422);
		}
    }
  
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json([$request->user()]);
    }
}