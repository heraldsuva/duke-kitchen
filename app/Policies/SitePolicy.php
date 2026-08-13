<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

class SitePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Site $site): bool
    {
        return $user->isSuperAdmin() || $site->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Site $site): bool
    {
        return $user->isSuperAdmin() || $site->user_id === $user->id;
    }

    public function delete(User $user, Site $site): bool
    {
        return $user->isSuperAdmin() || $site->user_id === $user->id;
    }

    public function manageEquipment(User $user, Site $site): bool
    {
        return $this->view($user, $site);
    }
}
