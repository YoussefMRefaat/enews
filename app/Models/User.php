<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use function Illuminate\Events\queueable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'roles',
        'publisher',
        'banned',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(queueable(function () {
            Cache::put('clerks' , static::withCount('topics')->orderBy('topics_count' , 'desc')->paginate(25));
        }));

        static::saved(queueable(function ($clerk){
            Cache::put('clerk_'.$clerk->id , $clerk->load('topics:id,title,published' , 'topics.tags:id,name' , 'topics.categories:id,name'));

            $clerk->topics()->update();
        }));

        static::deleted(queueable(function ($clerk){
            Cache::forget('clerk_'.$clerk->id);
            Cache::put('clerk_'.$clerk->id , $clerk->load('topics:id,title,published' , 'topics.tags:id,name' , 'topics.categories:id,name'));

            $clerk->topics()->delete();
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
        return Cache::rememberForever('clerk_'.$value , function ($value){
            return static::with(['topics:id,title,published' , 'topics.tags:id,name' , 'topics.categories:id,name'])->findOrFail($value);
        });
    }

    /**
     * Set the relationship between the clerk and his topics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function topics(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Topic::class , 'clerk_id');
    }

    /**
     * Interact with the user roles
     *
     * @return Attribute
     */
    protected function roles(): Attribute
    {
        return Attribute::make(
            get:fn($value) => explode(',' , $value),
            set:fn($value) => implode(',' , $value),
        );
    }

    public function scopePublic($query){

    }

}
