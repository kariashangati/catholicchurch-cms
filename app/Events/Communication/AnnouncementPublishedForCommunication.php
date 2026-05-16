<?php

namespace App\Events\Communication;

use App\Models\Announcement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnnouncementPublishedForCommunication
{
    use Dispatchable, SerializesModels;

    public function __construct(public Announcement $announcement)
    {
    }
}
