<?php

namespace App\Models;

use App\Traits\ModelCacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
//    public array $publicColumns = [
//        'name',
//        'parent_id',
//        'topics',
//    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(queueable(function ($category){
            $category->cache();
            $category->indexCache();
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
        $category =  $this->findFromCache($value);
        // Get related data from cache
        if (request()->method() == 'GET'){
            $category->parent = Category::index()->find($category->parent_id);
            $category->children = Category::index()->where('parent_id' , $value);
            $category->topics = Topic::index()->where('category_id' , $value);
        }
        return $category;
    }

    /**
     * Get models from the cache.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function scopeIndex(): \Illuminate\Pagination\LengthAwarePaginator
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
