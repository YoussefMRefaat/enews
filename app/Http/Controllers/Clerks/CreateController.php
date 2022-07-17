<?php

namespace App\Http\Controllers\Clerks;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clerks\StoreRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CreateController extends Controller
{

    /**
     * Store a moderator, writer, and/or journalist
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $this->prepareData($request->validated());

        $user = User::create($data);

        return response()->json([
            'message' => 'User has been created successfully',
            'id' => $user->id,
        ], 201);
    }

    /**
     * Prepare the data before storing
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data['password'] = Hash::make($data['password']);
        if (!isset($data['publisher']) && in_array(Roles::Moderator->value , $data['roles']))
            $data['publisher'] = true;
        return $data;
    }

}
