<?php

namespace App\Http\Controllers\Categories;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class DeleteController extends Controller
{

    /**
     * Delete a category
     *
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category){
        $category->load('topics');

        $this->handleTopics($category);
        $category->delete();

        return response()->json(status: 204);
    }


    /**
     * Handle topics of the deleted category
     *
     * @param $category
     * @return void
     */
    private function handleTopics($category): void
    {
        if($category->parent_id){
            $category->children()->update(['parent_id' => $category->parent_id]);
            $category->topics()->update(['category_id' => $category->parent_id]);
        }else{
            $category->topics()->delete();
        }
    }


}
