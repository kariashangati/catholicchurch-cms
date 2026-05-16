<?php

namespace App\Events\Communication;

use App\Models\Member;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberUpdatedForCommunication
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Member $member,
        public array $changes = []
    ) {
    }
}
