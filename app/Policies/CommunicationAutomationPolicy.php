<?php

namespace App\Policies;

use App\Models\CommunicationAutomation;
use App\Models\User;

class CommunicationAutomationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communication.manage_automations') || $user->can('communication.view');
    }

    public function view(User $user, CommunicationAutomation $automation): bool
    {
        return $user->can('communication.manage_automations') || $user->can('communication.view');
    }

    public function create(User $user): bool
    {
        return $user->can('communication.manage_automations');
    }

    public function update(User $user, CommunicationAutomation $automation): bool
    {
        return $user->can('communication.manage_automations');
    }

    public function delete(User $user, CommunicationAutomation $automation): bool
    {
        return $user->can('communication.manage_automations');
    }
}
