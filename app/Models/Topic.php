<?php

namespace App\Models;

use App\Enums\TopicType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use function Illuminate\Events\queueable;

class Topic extends Model
{
    use HasFactory;

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
        static::created(queueable(function ($topic) {
            Cache::put('topics' , static::with(['clerk:id,name' , 'category:id,name' , 'tags:id,name'])
                ->orderBy('updated_at' , 'desc')
                ->paginate(25));
        }));

        static::saved(queueable(function ($topic){
            Cache::put('topic_'.$topic->id , $topic->load('clerk:id,name' , 'category:id,name' , 'tags:id,name'));

//            $topic->category()->update();
//            $topic->clerk()->update();
//            $topic->tags()->update();
        }));

        static::deleted(queueable(function ($topic){
            Cache::forget('topic_'.$topic->id);
            Cache::put('topic_'.$topic->id , $topic->load('clerk:id,name' , 'category:id,name' , 'tags:id,name'));

//            $topic->category()->update();
//            $topic->clerk()->update();
//            $topic->tags()->update();
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
        return Cache::rememberForever('topic_'.$value , function ($value){
            return static::with(['clerk:id,name' , 'category:id,name' , 'tags:id,name'])->findOrFail($value);
        });
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

    public function tinyDescription(){} // get first n words of the description
}
