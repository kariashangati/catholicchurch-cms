<?php

namespace App\Events\Communication;

use App\Models\ApostolicGroupMember;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApostolicGroupJoinedForCommunication
{
    use Dispatchable, SerializesModels;

    public function __construct(public ApostolicGroupMember $membership)
    {
    }
}
