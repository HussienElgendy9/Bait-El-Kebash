<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $actor, User $user): bool
    {
        return $actor->isAdmin() && $actor->id !== $user->id;
    }

    public function delete(User $actor, User $user): bool
    {
        return $actor->isAdmin() && $actor->id !== $user->id && ! $user->isAdmin();
    }
}
