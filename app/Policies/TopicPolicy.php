<?php

namespace App\Policies;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TopicPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    /**
     * Check if the user can manage the topic
     *
     * @param User $user
     * @param Topic $topic
     * @return bool
     */
    public function manage(User $user , Topic $topic): bool
    {
        $roles = [\App\Enums\Roles::Admin->value , \App\Enums\Roles::Moderator->value];

        return $user->id == $topic->clerk_id || array_intersect($user->roles , $roles);
    }
}
