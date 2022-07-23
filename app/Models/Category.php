<?php

namespace App\Models;

use App\Traits\ModelCacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use function Illuminate\Events\queueable;

class Category extends Model
{
    use HasFactory, ModelCacher;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'parent_id',
        'enabled',
    ];

    /**
     * Columns that are available for public
     *
     * @var array
     */
    protected array $publicColumns = [
        'name',
        'parent_id',
        'topics',
    ];

    /**
     * Relations will be cached with the entity
     *
     * @var array|string[]
     */
    public array $cacheRelations = [
        'topics:id,title,published',
        'topics.tags:id,name',
        'topics.clerk:id,name',
        'parent:id,name',
        'children:id,name',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(queueable(function ($category){
            $category->cache($this->cacheRelations);
            $category->indexCache();
            $category->topics()->cache();
        }));

        static::deleting(queueable(function ($category){
            $category->children()->update(['parent_id' => $category->parent_id]);
            if($category->parent_id){
                $category->topics()->update(['category_id' => $category->parent_id]);
            }else{
                $category->topics()->delete();
            }
        }));

        static::deleted(queueable(function ($category){
            $category->dropCache();
            $category->indexCache();
        }));
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null): ?Model
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
     * Set the relationship between the category and its parent category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class , 'parent_id');
    }

    /**
     * Set the relationship between the category and children that belong to it
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function children(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(self::class , 'parent_id');
    }

    /**
     * Set the relationship between the category and topics that belong to it
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function topics(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Topic::class);
    }
}
