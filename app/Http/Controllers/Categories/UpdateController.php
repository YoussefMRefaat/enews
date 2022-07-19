<?php

namespace App\Http\Controllers\Categories;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\UpdateRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class UpdateController extends Controller
{

    /**
     * Update a category
     *
     * @param Category $category
     * @param UpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Category $category , UpdateRequest $request): \Illuminate\Http\JsonResponse
    {
        $category->update($request->validated());
        return response()->json(status: 204);
    }


    /**
     * Enable/disable a category
     *
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Category $category): \Illuminate\Http\JsonResponse
    {
        if ($category->parent && !$category->parent->enabled)
            abort(409 , 'Parent category is disabled');

        $category->update(['enabled' => !$category->enabled]);
        if (!$category->enabled)
            $category->children()->update(['enabled' => false]);

        return response()->json(status: 204);
    }


}
