<?php

namespace App\Http\Controllers\Topics;

use App\Enums\TopicType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Topics\StoreRequest;
use App\Models\Topic;
use Illuminate\Http\Request;

class CreateController extends Controller
{

    /**
     * Store an article
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeArticle(StoreRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $this->prepareData($request->validated() , TopicType::Article->value);

        return $this->store($data);
    }


    /**
     * Store news
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeNews(StoreRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $this->prepareData($request->validated() , TopicType::News->value);

        return $this->store($data);
    }


    /**
     * Store a topic
     *
     */
    private function store($data): \Illuminate\Http\JsonResponse
    {
        $topic = Topic::create($data);

        if ($data['tags'])
            $topic->tags()->attach($data['tags']);

        return response()->json([
            'message' => 'Topic has been created successfully',
            'id' => $topic->id,
        ], 201);
    }


    /**
     * Prepare data for storing
     *
     * @param array $data
     * @param string $type
     * @return array
     */
    private function prepareData(array $data , string $type): array
    {
        $data['type'] = $type;

        if (!auth()->user()->publisher)
            $data['published'] = false;

        return $data;
    }

}
