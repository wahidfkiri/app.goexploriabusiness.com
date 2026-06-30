<?php

namespace Vendor\MapsDataEngine\Policies;

use App\Models\User;
use Vendor\MapsDataEngine\Models\MapBusinessListing;

class MapBusinessListingPolicy
{
    public function viewAny(User $user): bool
    {
        return method_exists($user, 'hasRole') ? ($user->hasRole('admin') || $user->hasRole('super-admin')) : (bool) ($user->is_admin ?? false);
    }

    public function view(User $user, MapBusinessListing $listing): bool
    {
        return $this->viewAny($user);
    }
}
