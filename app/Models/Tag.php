<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use function Illuminate\Events\queueable;

class Tag extends Model
{
    use HasFactory;

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
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(queueable(function ($tag) {
            Cache::put('tags' , static::withCount('topics')->orderBy('topics_count' , 'desc')->paginate(25));
        }));

        static::saved(queueable(function ($tag){
            Cache::put('tag_'.$tag->id , $tag->load('topics:id,title,published' , 'topics.clerk:id,name' , 'topics.category:id,name'));

            $tag->topics()->update();
        }));

        static::deleted(queueable(function ($tag){
            Cache::forget('tag_'.$tag->id);
            Cache::put('tag_'.$tag->id , $tag->load('topics:id,title,published' , 'topics.clerk:id,name' , 'topics.category:id,name'));

            $tag->topics()->update();
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
        return Cache::rememberForever('tag_'.$value , function ($value){
            return static::with(['topics:id,title,published' , 'topics.clerk:id,name' , 'topics.category:id,name'])->findOrFail($value);
        });
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
