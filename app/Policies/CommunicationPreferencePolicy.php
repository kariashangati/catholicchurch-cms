<?php

namespace App\Policies;

use App\Models\Communication\CommunicationPreference;
use App\Models\User;

class CommunicationPreferencePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communication.view');
    }

    public function view(User $user, CommunicationPreference $communicationPreference): bool
    {
        return $user->can('communication.view');
    }

    public function update(User $user, CommunicationPreference $communicationPreference): bool
    {
        return $user->can('communication.manage_preferences');
    }
}
