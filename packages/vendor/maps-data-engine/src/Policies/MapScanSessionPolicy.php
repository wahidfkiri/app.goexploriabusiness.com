<?php

namespace Vendor\MapsDataEngine\Policies;

use App\Models\User;
use Vendor\MapsDataEngine\Models\MapScanSession;

class MapScanSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return method_exists($user, 'hasRole') ? ($user->hasRole('admin') || $user->hasRole('super-admin')) : (bool) ($user->is_admin ?? false);
    }

    public function view(User $user, MapScanSession $session): bool
    {
        return $this->viewAny($user);
    }
}
