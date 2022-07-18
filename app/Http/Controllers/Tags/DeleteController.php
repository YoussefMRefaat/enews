<?php

namespace App\Http\Controllers\Tags;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class DeleteController extends Controller
{

    /**
     * Delete a tag
     *
     * @param Tag $tag
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Tag $tag): \Illuminate\Http\JsonResponse
    {
        $tag->delete();
        return response()->json(status: 204);
    }

}
