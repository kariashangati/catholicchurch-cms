<?php

namespace App\Events\Finance;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferingRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public mixed $offering)
    {
    }
}
