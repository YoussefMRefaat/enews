<?php

namespace App\Http\Controllers\Tags;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class UpdateController extends Controller
{

    /**
     * Update the data of the tag
     *
     * @param Tag $tag
     * @param StoreTagRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Tag $tag ,  StoreTagRequest $request): \Illuminate\Http\JsonResponse
    {
        $tag->update($request->validated());

        return response()->json([
            'message' => 'Tag has been created successfully',
            'id' => $tag->id,
        ], 201);
    }


    /**
     * Enable/disable a tag
     *
     * @param Tag $tag
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Tag $tag): \Illuminate\Http\JsonResponse
    {
        $tag->update(['enabled' => !$tag->enabled]);
        return response()->json(status: 204);
    }

}
