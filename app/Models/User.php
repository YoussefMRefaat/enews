<?php

namespace App\Models;

use App\Enums\Roles;
use App\Traits\ModelCacher;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use function Illuminate\Events\queueable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, ModelCacher;

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
        static::saved(queueable(function ($clerk){
            $clerk->cache();
            $clerk->indexCache();
        }));

        static::deleting(queueable(function ($clerk){
            $clerk->topics()->delete();
        }));

        static::deleted(queueable(function ($clerk){
            $clerk->dropCache();
            $clerk->indexCache();
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
        $user = $this->findFromCache($value);

        return request()->segment(2) == 'dashboard'
            ? $this->findDashboard($user)
            : $this->findPublic($user);
    }

    /**
     * Retrieve the model for the dashboard
     *
     * @param $user
     * @return Model|null
     */
    protected function findDashboard($user): ?Model
    {
        // Get related data from cache
        if (request()->method() == 'GET'){
            $user->topics = Topic::index()->where('clerk_id' , $user->id);
        }
        return $user;
    }

    /**
     * Retrieve the model for the public
     *
     * @param $user
     * @return Model|null
     */
    protected function findPublic($user): ?Model
    {
        abort_if(!array_intersect($user->roles, [Roles::Journalist->value, Roles::Writer->value]), 404);
         // Get related data from cache
        if (request()->method() == 'GET'){
            $user->news = Topic::publicNews()->where('clerk.id' , $user->id);
            $user->articles = Topic::publicArticles()->where('clerk.id' , $user->id);
        }
        return $user->setVisible(['id' , 'name' , 'news' , 'articles']);
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
     * @return \Illuminate\Support\Collection
     */
    public function scopePublicIndex(): \Illuminate\Support\Collection
    {
        $users =  $this->getFromCache()->where('banned' , false)->filter(function ($value){
                return array_intersect($value->roles , [Roles::Journalist->value , Roles::Writer->value]);
            });

        return $users->map->only(['id' , 'name' , 'topics_count']);
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

}
