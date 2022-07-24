<?php

namespace App\Models;

use App\Enums\TopicType;
use App\Traits\ModelCacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use function Illuminate\Events\queueable;

class Topic extends Model
{
    use HasFactory, ModelCacher;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'clerk_id',
        'category_id',
        'type',
        'title',
        'body',
        'published',
        'published_at'
    ];

    /**
     * Columns that are available for public
     *
     * @var array
     */
//    public array $publicColumns = [
//        'name',
//        'type',
//        'title',
//        'body',
//        'published_at',
//        'clerk',
//        'category',
//        'tags',
//    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => TopicType::class,
        'published_at' => 'timestamp',
    ];

    /**
     * Relations will be cached with the entity
     *
     * @var array<string>
     */
    public array $cacheRelations = [
        'clerk:id,name',
        'category:id,name',
        'tags:id,name',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(queueable(function ($topic){
            $topic->indexCache('published_at' , 'desc' , $this->cacheRelations , null);
            $topic->cache($this->cacheRelations);

            // refresh caching
            $topic->category()->update();
            $topic->clerk()->update();
            $topic->tags()->update();
        }));

        static::deleted(queueable(function ($topic){
            $topic->drppCache();
            $topic->indexCache('published_at' , 'desc' , $this->cacheRelations , null);

            // refresh caching
            $topic->category()->update();
            $topic->clerk()->update();
            $topic->tags()->update();
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
        return $this->getFromCache('published_at' , 'desc' , $this->cacheRelations , false);
    }

    /**
     * Set the relationship between the topic and the clerk who created it
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class , 'clerk_id');
    }

    /**
     * Set the relationship between the topic and its tags
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class , 'tag_topic');
    }

    /**
     * Set the relationship between the topic and its category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function publicScope(){} // scope for accessing public topics

}
