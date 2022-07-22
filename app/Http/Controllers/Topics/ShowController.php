<?php

namespace App\Http\Controllers\Topics;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShowController extends Controller
{

    /**
     * Get all topics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $topics = Cache::rememberForever('topics' , function(){
            Topic::with(['clerk:id,name' , 'category:id,name' , 'tags:id,name'])
                ->orderBy('updated_at' , 'desc')
                ->paginate(25);
        });

        return response()->json($topics , 200);
    }


    /**
     * Show a topic
     *
     * @param Topic $topic
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Topic $topic): \Illuminate\Http\JsonResponse
    {
        return response()->json($topic , 200);
    }

}
