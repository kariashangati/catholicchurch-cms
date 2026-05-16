<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContributionType extends Model
{
    use HasFactory;

    protected $table = 'contribution_types';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'has_installments',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_installments' => 'boolean',
    ];

    public function plan(): HasOne
    {
        return $this->hasOne(ContributionTypePlan::class, 'contribution_type_id');
    }

    public function cashContributions(): HasMany
    {
        return $this->hasMany(CashContribution::class, 'contribution_type_id');
    }

    public function bankContributions(): HasMany
    {
        return $this->hasMany(BankContribution::class, 'contribution_type_id');
    }
}
