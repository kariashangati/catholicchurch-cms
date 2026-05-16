<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessageStatusLog extends Model
{
    protected $fillable = [
        'contact_message_id',
        'old_status',
        'new_status',
        'note',
        'changed_by',
    ];

    public function contactMessage()
    {
        return $this->belongsTo(ContactMessage::class);
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
