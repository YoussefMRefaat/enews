<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Jobs\SendResetPasswordToken;
use App\Mail\PasswordReset;
use App\Models\User;
use App\Traits\TokenHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class GuestController extends Controller
{
    use TokenHandler;


    /**
     * Handle the login request
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        if(!auth()->attempt($request->validated()))
            abort(401 , 'Invalid email or password');

        if (auth()->user()->banned)
            abort(403 , 'User has been banned');

        $token = auth()->user()->createToken('login');

        return response()->json([
            'message' => 'User logged in successfully',
            'token' => $token->plainTextToken,
            'roles' => auth()->user()->roles,
        ] , 200);
    }


    /**
     * Handle password forgotten request
     *
     * @param ForgetPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgetPassword(ForgetPasswordRequest $request): \Illuminate\Http\JsonResponse
    {
        $token = $this->storeToken($this->table , $this->indexKey , $request->validated('email'));
        SendResetPasswordToken::dispatch($request->validated('email') , $token , $this->seconds);
        return response()->json([
            'message' => 'Token is being sent',
        ] , 202);
    }


    /**
     * Handle reset password request
     *
     * @param ResetPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->getToken($this->seconds , $this->table , $this->indexKey , $request->validated('email') , $request->validated('token'));

        if(! User::where('email' , $request->validated('email'))
            ->update(['password' => Hash::make($request->validated('password'))]))
            abort(500);

        return response()->json([
            'message' => 'Password updated successfully',
        ] , 200);
    }

}
