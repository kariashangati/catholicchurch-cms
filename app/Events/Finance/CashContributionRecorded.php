<?php

namespace App\Events\Finance;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CashContributionRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public mixed $cashContribution)
    {
    }
}
