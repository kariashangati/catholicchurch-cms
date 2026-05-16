<?php

namespace App\Support\Communication;

use App\Events\Finance\BankContributionApproved;
use App\Events\Finance\CashContributionRecorded;
use App\Events\Finance\OfferingRecorded;
use App\Events\Finance\TitheRecorded;

class FinanceCommunicationDispatchExamples
{
    public static function titheRecorded(mixed $tithe): void
    {
        event(new TitheRecorded($tithe));
    }

    public static function offeringRecorded(mixed $offering): void
    {
        event(new OfferingRecorded($offering));
    }

    public static function cashContributionRecorded(mixed $cashContribution): void
    {
        event(new CashContributionRecorded($cashContribution));
    }

    public static function bankContributionApproved(mixed $bankContribution): void
    {
        event(new BankContributionApproved($bankContribution));
    }
}
