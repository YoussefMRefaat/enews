<?php

namespace App\Http\Controllers\Tags;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class CreateController extends Controller
{

    /**
     * Store a new tag
     *
     * @param StoreTagRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreTagRequest $request): \Illuminate\Http\JsonResponse
    {
        $tag = Tag::create($request->validated());

        return response()->json([
            'message' => 'Tag has been created successfully',
            'id' => $tag->id,
        ], 201);
    }

}
