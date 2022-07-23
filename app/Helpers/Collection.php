<?php

use Illuminate\Pagination\LengthAwarePaginator;
/**
 * Paginate a collection or array
 *
 * @param array|Collection      $items
 * @param int   $perPage
 * @param int  $page
 * @param array $options
 *
 * @return LengthAwarePaginator
 */
if(!function_exists('collectionPaginate')){
    function collectionPaginate($items, $perPage = 15, $page = null, $options = []): LengthAwarePaginator
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, $options);
    }
}

