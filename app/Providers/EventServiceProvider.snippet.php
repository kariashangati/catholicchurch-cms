<?php

use App\Events\Finance\BankContributionApproved;
use App\Events\Finance\CashContributionRecorded;
use App\Events\Finance\OfferingRecorded;
use App\Events\Finance\TitheRecorded;
use App\Listeners\Finance\SendBankContributionApprovedSmsListener;
use App\Listeners\Finance\SendCashContributionRecordedSmsListener;
use App\Listeners\Finance\SendOfferingRecordedSmsListener;
use App\Listeners\Finance\SendTitheRecordedSmsListener;

use App\Events\Communication\AnnouncementPublishedForCommunication;
use App\Events\Communication\ApostolicGroupJoinedForCommunication;
use App\Events\Communication\LeadershipAssignedForCommunication;
use App\Events\Communication\MemberCreatedForCommunication;
use App\Events\Communication\MemberUpdatedForCommunication;
use App\Listeners\Communication\SendAnnouncementPublishedAutomation;
use App\Listeners\Communication\SendApostolicGroupJoinedAutomation;
use App\Listeners\Communication\SendLeadershipAssignedAutomation;
use App\Listeners\Communication\SendMemberCreatedAutomation;
use App\Listeners\Communication\SendMemberUpdatedAutomation;

protected $listen = [
    TitheRecorded::class => [
        SendTitheRecordedSmsListener::class,
    ],
    OfferingRecorded::class => [
        SendOfferingRecordedSmsListener::class,
    ],
    CashContributionRecorded::class => [
        SendCashContributionRecordedSmsListener::class,
    ],
    BankContributionApproved::class => [
        SendBankContributionApprovedSmsListener::class,
    ],
	MemberCreatedForCommunication::class => [
        SendMemberCreatedAutomation::class,
    ],
    MemberUpdatedForCommunication::class => [
        SendMemberUpdatedAutomation::class,
    ],
    LeadershipAssignedForCommunication::class => [
        SendLeadershipAssignedAutomation::class,
    ],
    ApostolicGroupJoinedForCommunication::class => [
        SendApostolicGroupJoinedAutomation::class,
    ],
    AnnouncementPublishedForCommunication::class => [
        SendAnnouncementPublishedAutomation::class,
    ],
];













