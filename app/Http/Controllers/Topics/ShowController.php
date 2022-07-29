<?php

namespace App\Http\Controllers\Topics;

use App\Enums\TopicType;
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
        $topics = Topic::index();

        return response()->json($topics , 200);
    }

    /**
     * Get published news
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function news(): \Illuminate\Http\JsonResponse
    {
        $topics = Topic::publicNews();

        return response()->json($topics , 200);
    }

    /**
     * Get published articles
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function articles(): \Illuminate\Http\JsonResponse
    {
        $topics = Topic::publicArticles();

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
