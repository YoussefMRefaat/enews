<?php

namespace App\Models;

use App\Enums\TopicType;
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
//    public array $publicColumns = [
//        'name',
//        'topics',
//    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(queueable(function ($tag){
            $tag->indexCache();
            $tag->cache('topics');
        }));

        static::deleted(queueable(function ($tag){
            $tag->dropCache();
            $tag->indexCache();
        }));
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     * @param null $field
     * @return Model|null
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $tag = $this->findFromCache($value , 'topics');

        return request()->segment(2) == 'dashboard'
            ? $this->findDashboard($tag)
            : $this->findPublic($tag);
    }

    /**
     * Retrieve the model for the dashboard
     *
     * @param $tag
     * @return Model|null
     */
    protected function findDashboard($tag): ?Model
    {
        // Get related data from cache
        if (request()->method() == 'GET'){
            $tag->relatedTopics = Topic::index()->filter(function ($topic) use($tag){
                return in_array($tag->id , $topic['relatedTags']->pluck('id')->toArray());
            });
        }
        return $tag->setVisible(['id' , 'name' , 'enabled' , 'relatedTopics']);
    }

    /**
     * Retrieve the model for the public
     *
     * @param $tag
     * @return Model|null
     */
    protected function findPublic($tag): ?Model
    {
        if (!$tag->enabled) abort(404);// Get related data from cache
        if (request()->method() == 'GET'){
            $tag->news = Topic::publicNews()->filter(function ($topic) use ($tag){
                return in_array($tag->id , $topic['relatedTags']->pluck('id')->toArray());
            });
            $tag->articles = Topic::publicArticles()->filter(function ($topic) use ($tag){
                return in_array($tag->id , $topic['relatedTags']->pluck('id')->toArray());
            });
        }
        return $tag->setVisible(['id' , 'name' , 'enabled' , 'news' , 'articles']);
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
     * Get public entities from the cache.
     *
     * @return \Illuminate\Support\Collection
     */
    public function scopePublicIndex(): \Illuminate\Support\Collection
    {
        return $this->getFromCache()->where('enabled' , true)
            ->map->only(['id' , 'name' , 'enabled' , 'topics_count']);
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
