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
     * @return Model|array
     */
    public function resolveRouteBinding($value, $field = null): Model|array
    {
        $category =  $this->findFromCache($value);
        // if route is public -> find public || if route is dashboard -> find dashboard

        return $this->findPublic($category);
    }

    protected function findDashboard($category): ?Model
    {
        // Get related data from cache
        if (request()->method() == 'GET'){
            $category->parent = Category::index()->find($category->parent_id);
            $category->children = Category::index()->where('parent_id' , $category->id);
            $category->topics = Topic::index()->where('category_id' , $category->id);
            $category->topics->each(function ($topic){
                $topic->clerk = User::index()->find($topic->clerk_id);
            });
        }
        return $category;
    }

    protected function findPublic($category)
    {
        if (!$category->enabled) abort(404);
        // Get related data from cache
        if (request()->method() == 'GET'){
            $category->parent = Category::index()->where('id' , $category->parent_id)
                ->map->only(['id' , 'name' , 'topics_count']);
            $category->children = Category::index()->where('parent_id' , $category->id)
                ->where('enabled' , true)
                ->map->only(['id' , 'name' , 'topics_count']);
            $category->topics = Topic::index()->where('category_id' , $category->id)
                ->where('published' , true);
            $category->topics->each(function ($topic){
                $topic->clerk = User::index()->find($topic->clerk_id)->only(['id' , 'name']);
            });
            $category->topics = $category->topics->map->only(['id' , 'title' , 'body' ,'created_at' , 'clerk']);
        }
        return $category;//->only(['id' , 'name' , 'parent' , 'children' , 'topics']);
    }

    /**
     * Get entities from the cache.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function scopeIndex(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->getFromCache();
    }

    /**
     * Get public entities from the cache.
     *
     * @return Collection
     */
    public function scopePublicIndex(): \Illuminate\Support\Collection
    {
        return $this->getFromCache()->where('enabled' , true)
            ->map->only(['id' , 'name' , 'parent_id' , 'topics_count']);
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
