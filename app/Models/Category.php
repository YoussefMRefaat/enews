<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use function Illuminate\Events\queueable;

class Category extends Model
{
    use HasFactory;

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
        static::created(queueable(function () {
            Cache::put('categories' , static::withCount('topics')->orderBy('topics_count' , 'desc')->paginate(25));
        }));

        static::saved(queueable(function ($category){
            Cache::put('category_'.$category->id , $category->load('topics:id,title,published' , 'topics.tags:id,name' , 'topics.clerk:id,name'));

            $category->topics()->update();
        }));

        static::deleting(queueable(function ($category){
            Cache::forget('category_'.$category->id);
            Cache::put('categories' , static::whereNot('category_id' , $category->id)->withCount('topics')->orderBy('topics_count' , 'desc')->paginate(25));

            $category->children()->update(['parent_id' => $category->parent_id]);
            if($category->parent_id){
                $category->topics()->update(['category_id' => $category->parent_id]);
            }else{
                $category->topics()->delete();
            }
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
        return Cache::rememberForever('category_'.$value , function ($value){
            return static::with(['topics:id,title,published' , 'topics.clerk:id,name' , 'topics.category:id,name' , 'parent:id,name' , 'children:id,name'])->findOrFail($value);
        });
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
