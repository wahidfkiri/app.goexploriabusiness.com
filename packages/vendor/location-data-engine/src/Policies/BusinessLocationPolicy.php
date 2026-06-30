<?php

namespace Vendor\LocationDataEngine\Policies;

use App\Models\User;
use Vendor\LocationDataEngine\Models\BusinessLocation;

class BusinessLocationPolicy
{
    public function viewAny(User $user): bool
    {
        return method_exists($user, 'hasRole') ? ($user->hasRole('admin') || $user->hasRole('super-admin')) : (bool) ($user->is_admin ?? false);
    }

    public function view(User $user, BusinessLocation $businessLocation): bool
    {
        return $this->viewAny($user);
    }
}
