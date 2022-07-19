<?php

namespace App\Http\Controllers\Clerks;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ShowController extends Controller
{

    /**
     * Get all users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $users = User::withCount('topics')->get();
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
        $user->load('topics' , 'topics.tags:id,name' , 'topics.categories:id,name')->paginate(25);
        return response()->json($user , 200);
    }

}
