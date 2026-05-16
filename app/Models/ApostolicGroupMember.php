<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApostolicGroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'apostolic_group_id',
        'member_id',
        'role',
        'status',
        'joined_at',
        'left_at',
        'notes',
        'added_by',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'left_at' => 'date',
        ];
    }

    public function apostolicGroup()
    {
        return $this->belongsTo(ApostolicGroup::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}