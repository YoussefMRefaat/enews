<?php

namespace App\Http\Controllers\Clerks;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clerks\UpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UpdateController extends Controller
{

    /**
     * Update the data of a clerk
     *
     * @param User $user
     * @param UpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(User $user , UpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $user->update($request->validated());
        return response()->json(status: 204);
    }


    /**
     * Mark a clerk as publisher/not publisher
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function publisher(User $user): \Illuminate\Http\JsonResponse
    {
        $user->update(['publisher' => !$user->publisher]);
        return response()->json(status: 204);
    }

    /**
     * Mark a clerk as banned/not banned
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function ban(User $user): \Illuminate\Http\JsonResponse
    {
        $update = $user->update(['banned' => !$user->banned]);

        if (!$update)
            abort(500);

        $user->tokens()->delete();
        return response()->json(status: 204);
    }


    /**
     * Hide all topics of the user
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function hide(User $user): \Illuminate\Http\JsonResponse
    {
        $user->topics()->update(['enabled' => 0]);
        return response()->json(status: 204);
    }

}
