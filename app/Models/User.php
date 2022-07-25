<?php

namespace App\Models;

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
     * Columns that are available for public
     *
     * @var array
     */
//    public array $publicColumns = [
//        'name',
//        'email',
//        'topics',
//    ];

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
     * Relations will be cached with the entity
     *
     * @var array|string[]
     */
    public array $cacheRelations = [];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saved(queueable(function ($clerk){
            $clerk->cache($clerk->cacheRelations);
            $clerk->indexCache();
//            $clerk->topics()->cache();
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
        return $this->findFromCache($value);
    }

    /**
     * Get models from the cache.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function index(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->getFromCache();
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
