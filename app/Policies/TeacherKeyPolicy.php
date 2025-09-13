<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TeacherKey;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeacherKeyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the teacher key.
     */
    public function view(User $user, TeacherKey $key): bool
    {
        return $user->id === $key->user_id;
    }

    /**
     * Determine if the user can update the teacher key.
     */
    public function update(User $user, TeacherKey $key): bool
    {
        return $user->id === $key->user_id;
    }

    /**
     * Determine if the user can delete the teacher key.
     */
    public function delete(User $user, TeacherKey $key): bool
    {
        return $user->id === $key->user_id;
    }
}
