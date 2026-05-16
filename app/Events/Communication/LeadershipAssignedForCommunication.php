<?php

namespace App\Events\Communication;

use App\Models\LeadershipAssignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadershipAssignedForCommunication
{
    use Dispatchable, SerializesModels;

    public function __construct(public LeadershipAssignment $assignment)
    {
    }
}
