<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait ModelCacher{

    /**
     * Cache an entity
     */
    public function cache(array $relations){
        Cache::put(strtolower(class_basename($this)) . '_' . $this->id , $this->load($this->cacheRelations));
    }

    /**
     * Cache the index of entities
     */
    public function indexCache(string $orderBy = 'topics_count' , string $orderDir = 'desc' , array|null $relations = null , string|null $countRelations = 'topics'){
        $entities = $this->indexCacheQuery($orderBy , $orderDir , $relations , $countRelations);
        Cache::put(Str::plural(strtolower(class_basename($this))) , $entities);
    }

    /**
     * Drop a cache of an entity
     */
    public function dropCache(){
        Cache::forget(strtolower(class_basename($this)) . '_' . $this->id);
    }

    public function findFromCache(int $value){
        $cache =  Cache::rememberForever(strtolower(get_class($this)) . '_' .$value , function ($value){
            return $this->with($this->cacheRelations)->findOrFail($value);
        });
        return auth()->check() ? $cache : $cache->only($this->publicColumns);
    }

    public function getFromCache(string $orderBy = 'topics_count' , string $orderDir = 'desc' , array|null $relations = null , string|null $countRelations = 'topics')
    {
        return Cache::rememberForever(Str::plural(strtolower(class_basename($this))) , function ($orderBy , $orderDir , $relations , $countRelations){
            $this->indexCacheQuery($orderBy , $orderDir , $relations , $countRelations);
        });
    }

    private function indexCacheQuery(string $orderBy, string $orderDir, array|null $relations, string|null $countRelations): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->orderBy($orderBy , $orderDir);
        $query = $relations ? $query->with($relations) : $query;
        $query = $countRelations ? $query->withCount($countRelations) : $query;

        return auth()->check() ? collectionPaginate($query->all() , 25) : collectionPaginate($query->only($this->publicColumns)->all() , 25);
    }

}
