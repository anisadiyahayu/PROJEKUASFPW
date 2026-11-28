<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function deactivate(User $user, User $target): bool
    {
        return ! $user->is($target);
    }
}
