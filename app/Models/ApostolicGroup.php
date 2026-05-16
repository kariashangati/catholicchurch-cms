<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApostolicGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'slug',
        'notes',
        'is_active',
        'leader_member_id',
        'assistant_leader_member_id',
        'patron_member_id',
        'membership_rule_type',
        'membership_rule_value',
        'image',
        'founded_on',
        'meeting_day',
        'meeting_time',
        'meeting_location',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'founded_on' => 'date',
            'meeting_time' => 'datetime:H:i',
        ];
    }

    public function leader()
    {
        return $this->belongsTo(Member::class, 'leader_member_id');
    }

    public function assistantLeader()
    {
        return $this->belongsTo(Member::class, 'assistant_leader_member_id');
    }

    public function patron()
    {
        return $this->belongsTo(Member::class, 'patron_member_id');
    }

    public function groupMembers()
    {
        return $this->hasMany(ApostolicGroupMember::class);
    }

    public function members()
    {
        return $this->belongsToMany(Member::class, 'apostolic_group_members')
            ->withPivot(['id', 'role', 'status', 'joined_at', 'left_at', 'notes', 'added_by'])
            ->withTimestamps();
    }
}