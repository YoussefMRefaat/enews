<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateUserDataRequest;
use App\Http\Requests\Auth\UpdateUserPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    /**
     * Update the user's data
     *
     * @param UpdateUserDataRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserDataRequest $request): \Illuminate\Http\JsonResponse
    {
        if(!auth()->user()->update($request->validated()))
            abort(500);
        return response()->json(status:204);
    }


    /**
     * Update the user's password
     *
     * @param UpdateUserPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(UpdateUserPasswordRequest $request): \Illuminate\Http\JsonResponse
    {
        if(!auth()->user()->update(['password' => Hash::make($request->validated('password'))]))
            abort(500);
        return response()->json(status:204);
    }


    /**
     * Logout the user from the current session
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): \Illuminate\Http\JsonResponse
    {
        auth()->user()->currentAccessToken()->delete();
        return response()->json(status:204);
    }


    /**
     * Delete all sessions of the user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutAllSessions(): \Illuminate\Http\JsonResponse
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'message' => 'User has been logged out from all sessions successfully'
        ] , 200);
    }

}
