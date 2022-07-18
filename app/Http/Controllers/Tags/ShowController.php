<?php

namespace App\Http\Controllers\Tags;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class ShowController extends Controller
{

    /**
     * Get all tags
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $tags = Tag::withCount('topics')->get();

        return response()->json($tags, 200);
    }


    /**
     * Show a tag
     *
     * @param Tag $tag
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Tag $tag): \Illuminate\Http\JsonResponse
    {
        $tag->load('topics')->paginate(25);
        return response()->json($tag, 200);
    }


}
