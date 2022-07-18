<?php

namespace App\Http\Controllers\Categories;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CreateController extends Controller
{

    /**
     * Store a new category
     *
     * @param StoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request): \Illuminate\Http\JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json([
            'message' => 'Category has been created successfully',
            'id' => $category->id,
        ], 201);
    }

}
