<?php

namespace App\Http\Controllers\Tags;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShowController extends Controller
{

    /**
     * Get all tags
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $tags = Cache::rememberForever('tags' , function (){
            Tag::withCount('topics')->orderBy('topics_count' , 'desc')->paginate(25);
        });

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
        return response()->json($tag, 200);
    }


}
