<?php

namespace App\Http\Controllers\Topics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Topics\UpdateRequest;
use App\Models\Topic;
use Illuminate\Http\Request;

class UpdateController extends Controller
{

    /**
     * Update a topic
     *
     * @param Topic $topic
     * @param UpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Topic $topic , UpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        if (!auth()->user()->publisher)
            $data['published'] = false;

        if (isset($data['tags'])){
            $topic->tags()->detach();
            $topic->tags()->attach($data['tags']);
        }

        $topic->update($request->validated());

        return response()->json(status: 204);
    }


    /**
     * Publish a topic
     *
     * @param Topic $topic
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish(Topic $topic): \Illuminate\Http\JsonResponse
    {
        if (!auth()->user()->publisher)
            abort(403);

        $data = ['published' => !$topic->published];
        if (!$topic->published && !$topic->published_at)
            $data['published_at'] = now();

        $topic->update($data);

        return response()->json(status: 204);
    }

}
