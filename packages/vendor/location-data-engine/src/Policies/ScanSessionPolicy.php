<?php

namespace Vendor\LocationDataEngine\Policies;

use App\Models\User;
use Vendor\LocationDataEngine\Models\ScanSession;

class ScanSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return method_exists($user, 'hasRole') ? ($user->hasRole('admin') || $user->hasRole('super-admin')) : (bool) ($user->is_admin ?? false);
    }

    public function view(User $user, ScanSession $scanSession): bool
    {
        return $this->viewAny($user);
    }
}
