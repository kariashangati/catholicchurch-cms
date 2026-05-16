<?php

namespace App\Events\Finance;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BankContributionApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(public mixed $bankContribution)
    {
    }
}
