<?php

namespace App\Http\Controllers\Categories;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShowController extends Controller
{

    /**
     * Get all categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $categories = Category::index();

        return response()->json($categories , 200);
    }


    /**
     * Show a category
     *
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Category $category): \Illuminate\Http\JsonResponse
    {
        return response()->json($category , 200);
    }

}
