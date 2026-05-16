<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetEstimateIncome extends Model
{
    use HasFactory;

    protected $table = 'budget_estimate_incomes';

    protected $fillable = [
        'category_code',
        'category_name',
        'group_name',
        'amount',
        'budget_year',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'budget_year' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
