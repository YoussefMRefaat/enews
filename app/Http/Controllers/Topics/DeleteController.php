<?php

namespace App\Http\Controllers\Topics;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\Request;

class DeleteController extends Controller
{

    /**
     * Delete a topic
     *
     * @param Topic $topic
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Topic $topic): \Illuminate\Http\JsonResponse
    {
        $topic->delete();

        return response()->json(status: 204);
    }

}
