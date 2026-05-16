<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'familia_id',
        'first_name',
        'middle_name',
        'last_name',
        'phone',
        'gender',
        'date_of_birth',
        'occupation',
        'is_baptized',
        'has_communion',
        'has_confirmation',
        'receives_eucharist',
        'is_married',
        'marriage_type',
        'baptism_certificate_number',
        'marriage_certificate_number',
        'baptism_parish',
        'baptism_diocese',
        'family_role',
        'notes',
        'member_code',
        'bahasha',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_baptized' => 'boolean',
            'has_communion' => 'boolean',
            'has_confirmation' => 'boolean',
            'receives_eucharist' => 'boolean',
            'is_married' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function familia()
    {
        return $this->belongsTo(Familia::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])->filter()->implode(' '));
    }
	
	    public function apostolicGroupMemberships()
    {
        return $this->hasMany(ApostolicGroupMember::class);
    }
	
	    public function titheRecords()
    {
        return $this->hasMany(Tithe::class);
    }

    public function cashContributions()
    {
        return $this->hasMany(CashContribution::class);
    }
	public function mafundishoEnrollments()
{
    return $this->hasMany(\App\Models\MafundishoEnrollment::class);
}

    public function apostolicGroups()
    {
        return $this->belongsToMany(ApostolicGroup::class, 'apostolic_group_members')
            ->withPivot(['id', 'role', 'status', 'joined_at', 'left_at', 'notes', 'added_by'])
            ->withTimestamps();
    }
	
	public function user()
{
    return $this->hasOne(\App\Models\User::class, 'member_id');
}


    public function bankContributions()
    {
        return $this->hasMany(\App\Models\BankContribution::class);
    }

    public function communicationPreference()
    {
        return $this->hasOne(\App\Models\CommunicationPreference::class);
    }

    public function getJumuiyaAttribute()
    {
        return $this->familia?->jumuiya;
    }

    public function getKandaAttribute()
    {
        return $this->familia?->jumuiya?->kanda;
    }

}
