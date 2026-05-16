<?php

namespace App\Services\Finance;

use App\Models\ContributionType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ContributionTypeService
{
    public function create(array $data): ContributionType
    {
        return DB::transaction(function () use ($data) {
            $type = ContributionType::create(Arr::only($data, [
                'name',
                'slug',
                'description',
                'is_active',
                'has_installments',
            ]));

            $this->syncPlan($type, $data);

            return $type;
        });
    }

    public function update(ContributionType $type, array $data): ContributionType
    {
        return DB::transaction(function () use ($type, $data) {
            $type->update(Arr::only($data, [
                'name',
                'slug',
                'description',
                'is_active',
                'has_installments',
            ]));

            $this->syncPlan($type, $data);

            return $type->fresh('plan');
        });
    }

    protected function syncPlan(ContributionType $type, array $data): void
    {
        $hasInstallments = (bool) ($data['has_installments'] ?? false);

        if (! $hasInstallments) {
            $type->plan()?->delete();
            return;
        }

        $type->plan()->updateOrCreate([], [
            'target_amount' => $data['target_amount'] ?? 0,
            'installments_count' => $data['installments_count'] ?? 1,
            'due_date' => $data['due_date'] ?? null,
        ]);
    }
}
