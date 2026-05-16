<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationTemplate extends Model
{
    use HasFactory, SoftDeletes;

    public const CHANNEL_SMS = 'sms';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'code',
        'channel',
        'category',
        'locale',
        'subject',
        'body',
        'variables',
        'event_key',
        'audience_type',
        'status',
        'is_system',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function automations()
    {
        return $this->hasMany(CommunicationAutomation::class, 'template_id');
    }

    public function campaigns()
    {
        return $this->hasMany(CommunicationCampaign::class, 'template_id');
    }

    public function messages()
    {
        return $this->hasMany(CommunicationMessage::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
