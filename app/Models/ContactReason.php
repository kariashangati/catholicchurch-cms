<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactReason extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function contactMessages()
    {
        return $this->hasMany(ContactMessage::class);
    }
}
