<?php

namespace App\Models;

use App\Traits\ModelCacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use function Illuminate\Events\queueable;

class Tag extends Model
{
    use HasFactory, ModelCacher;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'enabled',
    ];

    /**
     * Columns that are available for public
     *
     * @var array
     */
    protected array $publicColumns = [
        'name',
        'topics',
    ];

    /**
     * Relations will be cached with the entity
     *
     * @var array<string>
     */
    public array $cacheRelations = [
        'topics:id,title,published',
        'topics.category:id,name',
        'topics.clerk:id,name',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(queueable(function ($tag){
            $tag->indexCache();
            $tag->cache($this->cacheRelations);
            $tag->topics()->cache();
        }));

        static::deleted(queueable(function ($tag){
            $tag->dropCache();
            $tag->indexCache();
            $tag->topics()->cache();
        }));
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->findFromCache($value);
    }

    /**
     * Get models from the cache.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function index(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->getFromCache();
    }

    /**
     * Set the relationship between the tag and its topics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function topics(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Topic::class , 'tag_topic');
    }

}
