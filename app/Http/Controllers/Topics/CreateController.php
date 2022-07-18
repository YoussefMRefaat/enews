<?php

namespace App\Http\Controllers\Topics;

use App\Enums\TopicType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Topics\StoreRequest;
use App\Models\Topic;
use Illuminate\Http\Request;

class CreateController extends Controller
{

    public function storeArticle(StoreRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $this->prepareData($request->validated() , TopicType::Article->value);

        return $this->store($data);
    }


    public function storeNews(StoreRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $this->prepareData($request->validated() , TopicType::News->value);

        return $this->store($data);
    }


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

    private function prepareData(array $data , string $type): array
    {
        $data['type'] = $type;

        if (!auth()->user()->publisher)
            $data['published'] = false;

        return $data;
    }

}
