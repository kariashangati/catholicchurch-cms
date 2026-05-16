<?php

namespace App\Policies;

use App\Models\CommunicationCampaign;
use App\Models\User;

class CommunicationCampaignPolicy
{
    public function approve(User $user, CommunicationCampaign $campaign): bool
    {
        return $user->can('communication.approve_campaigns');
    }

    public function cancel(User $user, CommunicationCampaign $campaign): bool
    {
        return $user->can('communication.cancel_campaigns');
    }

    public function retryFailed(User $user, CommunicationCampaign $campaign): bool
    {
        return $user->can('communication.retry_failed');
    }
}
