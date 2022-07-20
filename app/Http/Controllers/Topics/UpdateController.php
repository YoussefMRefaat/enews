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

        $topic->update(['published' => !$topic->published]);

        return response()->json(status: 204);
    }

}
