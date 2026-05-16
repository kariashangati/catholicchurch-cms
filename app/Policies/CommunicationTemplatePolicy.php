<?php

namespace App\Policies;

use App\Models\CommunicationTemplate;
use App\Models\User;

class CommunicationTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communication.manage_templates') || $user->can('communication.view');
    }

    public function view(User $user, CommunicationTemplate $template): bool
    {
        return $user->can('communication.manage_templates') || $user->can('communication.view');
    }

    public function create(User $user): bool
    {
        return $user->can('communication.manage_templates');
    }

    public function update(User $user, CommunicationTemplate $template): bool
    {
        return $user->can('communication.manage_templates');
    }

    public function delete(User $user, CommunicationTemplate $template): bool
    {
        return $user->can('communication.manage_templates') && ! $template->is_system;
    }
}
