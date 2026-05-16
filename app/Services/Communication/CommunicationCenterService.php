<?php

namespace App\Services\Communication;

use App\Models\CommunicationAutomation;
use App\Models\CommunicationCampaign;
use App\Models\CommunicationTemplate;
use App\Services\Communication\Providers\BeemSmsService;

class CommunicationCenterService
{
    public function __construct(
        protected BeemSmsService $beemSmsService,
    ) {
    }

    /**
     * Phase one support service.
     *
     * Keeps central enums and small helper lookups in one place so the
     * module has a single source of truth before controllers/jobs are added.
     */
    public function statusOptions(): array
    {
        return [
            'template_statuses' => [
                CommunicationTemplate::STATUS_DRAFT,
                CommunicationTemplate::STATUS_ACTIVE,
                CommunicationTemplate::STATUS_INACTIVE,
                CommunicationTemplate::STATUS_ARCHIVED,
            ],
            'campaign_statuses' => [
                CommunicationCampaign::STATUS_DRAFT,
                CommunicationCampaign::STATUS_PENDING_APPROVAL,
                CommunicationCampaign::STATUS_APPROVED,
                CommunicationCampaign::STATUS_SCHEDULED,
                CommunicationCampaign::STATUS_PROCESSING,
                CommunicationCampaign::STATUS_COMPLETED,
                CommunicationCampaign::STATUS_PARTIALLY_FAILED,
                CommunicationCampaign::STATUS_FAILED,
                CommunicationCampaign::STATUS_CANCELLED,
            ],
            'automation_modes' => [
                CommunicationAutomation::MODE_IMMEDIATE,
                CommunicationAutomation::MODE_SCHEDULED,
                CommunicationAutomation::MODE_MANUAL_REVIEW,
            ],
        ];
    }

    public function supportedChannels(): array
    {
        return [CommunicationTemplate::CHANNEL_SMS];
    }

    public function currentSmsBalance(): array
    {
        return $this->beemSmsService->getBalance();
    }
}
