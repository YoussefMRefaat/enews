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
        $topics = Topic::index();

        return response()->json($topics , 200);
    }

    /**
     * Get public topics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function publicIndex(): \Illuminate\Http\JsonResponse
    {
        $topics = Topic::publicIndex();

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
