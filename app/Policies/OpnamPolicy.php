<?php

namespace App\Policies;

use App\Models\Opnam;
use App\Models\User;

class OpnamPolicy
{
    public function view(User $user, Opnam $opnam): bool
    {
        return (int) $user->id === (int) $opnam->created_by;
    }

    public function update(User $user, Opnam $opnam): bool
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return $opnam->status === Opnam::STATUS_MENUNGGU_REVIEW;
        }

        if ($user->hasRole(User::ROLE_DIREKSI)) {
            return $opnam->status === Opnam::STATUS_MENUNGGU_APPROVAL_DIREKSI;
        }

        return false;
    }
}
