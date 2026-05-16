<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationAutomation extends Model
{
    use HasFactory;

    public const MODE_IMMEDIATE = 'immediate';
    public const MODE_SCHEDULED = 'scheduled';
    public const MODE_MANUAL_REVIEW = 'manual_review';

    protected $fillable = [
        'name',
        'code',
        'event_key',
        'channel',
        'template_id',
        'is_enabled',
        'trigger_mode',
        'delay_minutes',
        'audience_type',
        'conditions',
        'respect_preferences',
        'respect_quiet_hours',
        'send_once_per_entity',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'conditions' => 'array',
            'respect_preferences' => 'boolean',
            'respect_quiet_hours' => 'boolean',
            'send_once_per_entity' => 'boolean',
            'delay_minutes' => 'integer',
        ];
    }

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class, 'template_id');
    }

    public function campaigns()
    {
        return $this->hasMany(CommunicationCampaign::class, 'automation_id');
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
