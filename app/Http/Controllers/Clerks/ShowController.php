<?php

namespace App\Http\Controllers\Clerks;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShowController extends Controller
{

    /**
     * Get all users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $users = User::index();

        return response()->json($users , 200);
    }

    /**
     * Get public users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicIndex(): \Illuminate\Http\JsonResponse
    {
        $users = User::publicIndex();

        return response()->json($users , 200);
    }

    /**
     * Show a specific user
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user): \Illuminate\Http\JsonResponse
    {
        return response()->json($user , 200);
    }

}
