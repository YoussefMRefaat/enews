<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait ModelCacher{

    /**
     * Cache an entity
     *
     * @return void
     */
    public function cache()
    {
        Cache::put(strtolower(class_basename(static::class)) . '_' . $this->id , $this);
    }

    /**
     * Cache the index of entities
     *
     * @return void
     */
    public function indexCache(string $orderBy = 'topics_count' , string $orderDir = 'desc' , bool $withCountTopics = true)
    {
        Cache::put(Str::plural(strtolower(class_basename(static::class))) , $this->indexCacheQuery($orderBy , $orderDir , $withCountTopics));
    }

    /**
     * Drop a cache of an entity
     *
     * @return void
     */
    public function dropCache()
    {
        Cache::forget(strtolower(class_basename(static::class)) . '_' . $this->id);
    }

    /**
     * Find an entity from the cache
     *
     * @param int $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findFromCache(int $value): ?\Illuminate\Database\Eloquent\Model
    {
        return  Cache::rememberForever(strtolower(class_basename(static::class)) . '_' .$value , function () use ($value){
            return $this->findOrFail($value);
        });
    }

    /**
     * Get entities from the cache
     *
     * @param string $orderBy
     * @param string $orderDir
     * @param bool $withCountTopics
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getFromCache(string $orderBy = 'topics_count' , string $orderDir = 'desc' , bool $withCountTopics = true): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Cache::rememberForever(Str::plural(strtolower(class_basename(static::class))) , function () use ($orderBy , $orderDir , $withCountTopics){
            $this->indexCacheQuery($orderBy , $orderDir , $withCountTopics);
        });
    }

    /**
     * Execute getting entities from the DB query
     *
     * @param string $orderBy
     * @param string $orderDir
     * @param bool $withCountTopics
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function indexCacheQuery(string $orderBy, string $orderDir, bool $withCountTopics): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->orderBy($orderBy , $orderDir);
        $query = $withCountTopics ? $query->withCount(['topics' => function (Builder $query){
            $query->where('published' , true);
        }]) : $query;

        return $query->paginate(25);
    }

}
