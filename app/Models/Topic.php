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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => TopicType::class,
        'published_at' => 'timestamp',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(queueable(function ($topic){
            $topic->indexCache('published_at' , 'desc' , false);
            $topic->cache('tags');
        }));

        static::deleted(queueable(function ($topic){
            $topic->drppCache();
            $topic->indexCache('published_at' , 'desc' , false);
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
        $topic = $this->findFromCache($value , 'tags');
        // Get related data from cache
        return request()->segment(2) == 'dashboard'
            ? $this->findDashboard($topic)
            : $this->findPublic($topic);
    }

    /**
     * Retrieve the model for the dashboard
     *
     * @param $topic
     * @return Model|null
     */
    protected function findDashboard($topic): ?Model
    {
        // Get related data from cache
        if (request()->method() == 'GET'){
            $topic->category = Category::index()->find($topic->category_id);
            $topic->clerk = User::index()->find($topic->clerk_id);
            $topic->relatedTags = Tag::index()->whereIn('id' , $topic['tags']->pluck('id')->toArray());
        }
        return $topic->setHidden(['tags']);
    }

    /**
     * Retrieve the model for the public
     *
     * @param $topic
     * @return Model|null
     */
    protected function findPublic($topic): ?Model
    {
        // Get related data from cache
        if (request()->method() == 'GET'){
            $topic->category = Category::publicIndex()->where('id' , $topic->category_id);
            $topic->clerk = User::publicIndex()->where('id' , $topic->clerk_id);
            $topic->relatedTags = Tag::publicIndex()->whereIn('id' , $topic['tags']->pluck('id')->toArray());
        }
        if (!$topic->category || !$topic->published)
            abort(404);
        return $topic->setVisible(['id' , 'title' , 'body' , 'published_at' , 'category' , 'clerk' , 'relatedTags']);
    }

    /**
     * Get entities from the cache.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function scopeIndex(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $topics = $this->getFromCache('published_at' , 'desc' , false);
        // load related data from cache
        $topics->each(function ($topic){
            $topic->category = Category::index()->where('id' , $topic->category_id);
            $topic->clerk = User::index()->where('id' , $topic->clerk_id);
            $topic->relatedTags = Tag::publicIndex()->whereIn('id' , $topic['tags']->pluck('id')->toArray());
        });
        return $topics;
    }

    /**
     * Get public entities from the cache.
     *
     * @return mixed
     */
    public function scopePublicNews(): mixed
    {
        $topics = $this->getFromCache('published_at' , 'desc' , false)
            ->where('type' , TopicType::News)->where('published' , true)
        ;
        // load related data from cache
        return $this->publicIndexRelations($topics);
    }

    /**
     * Get public entities from the cache.
     *
     * @return mixed
     */
    public function scopePublicArticles(): mixed
    {
        $topics = $this->getFromCache('published_at' , 'desc' , false)
            ->where('type' , TopicType::Article)->where('published' , true);
        // load related data from cache
        return $this->publicIndexRelations($topics);

    }

    /**
     * Get relation for public index
     *
     * @param $topics
     * @return mixed
     */
    private function publicIndexRelations($topics): mixed
    {
        $topics->each(function ($topic){
            $topic->category = Category::publicIndex()->where('id' ,$topic->category_id)->only(['id' , 'name']);
            $topic->clerk = User::publicIndex()->where('id' , $topic->clerk_id);
            $topic->relatedTags = Tag::publicIndex()->whereIn('id' , $topic['tags']->pluck('id')->toArray());
        });

        return $topics->map->only(['title' , 'body' , 'created_at' , 'category' , 'clerk' , 'relatedTags']);
    }

    /**
     * Set the relationship between the topic and the clerk who created it
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clerk(): \Illuminate\Database\Eloquent\Relations\BelongsTo
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

}
