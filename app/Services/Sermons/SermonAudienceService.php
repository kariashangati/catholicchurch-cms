<?php
namespace App\Services\Sermons;

use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\Sermon;
use App\Models\SermonRecipient;
use App\Models\SermonTarget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SermonAudienceService
{
    public function syncTargetsAndRecipients(Sermon $sermon, array $payload): void
    {
        DB::transaction(function () use ($sermon, $payload): void {
            $sermon->targets()->delete();
            $sermon->recipients()->delete();
            $sermon->tokens()->delete();

            $targetType = $payload['target_type'] ?? 'all';
            $targetIds = array_filter((array)($payload['target_ids'] ?? []));
            if ($targetType !== 'custom' && empty($targetIds) && ! in_array($targetType, ['all'], true)) {
                $targetIds = [$payload['target_id'] ?? null];
            }

            $members = $this->resolveMembers($targetType, $targetIds);

            $label = $this->targetLabel($targetType, $targetIds);
            $sermon->targets()->create([
                'target_type' => $targetType,
                'target_id' => count($targetIds) === 1 ? $targetIds[0] : null,
                'label' => $label,
                'meta' => ['target_ids' => array_values($targetIds)],
            ]);

            $members->each(function (Member $member) use ($sermon): void {
                if (blank($member->phone)) { return; }
                SermonRecipient::create([
                    'sermon_id' => $sermon->id,
                    'member_id' => $member->id,
                    'name' => $member->full_name ?: trim(($member->first_name ?? '').' '.($member->last_name ?? '')),
                    'phone' => $member->phone,
                    'email' => $member->user?->email,
                    'familia_id' => $member->familia_id,
                    'jumuiya_id' => $member->jumuiya?->id,
                    'kanda_id' => $member->kanda?->id,
                    'sms_status' => SermonRecipient::SMS_NOT_SENT,
                ]);
            });
        });
    }

    public function resolveMembers(string $targetType, array $targetIds = []): Collection
    {
        $query = Member::query()->with(['familia.jumuiya.kanda','user'])->where('is_active', true)->whereNotNull('phone');
        return match ($targetType) {
            'kanda' => $query->whereHas('familia.jumuiya', fn($q) => $q->whereIn('kanda_id', $targetIds))->get(),
            'jumuiya' => $query->whereHas('familia', fn($q) => $q->whereIn('jumuiya_id', $targetIds))->get(),
            'familia' => $query->whereIn('familia_id', $targetIds)->get(),
            'member', 'custom' => $query->whereIn('id', $targetIds)->get(),
            default => $query->get(),
        };
    }

    protected function targetLabel(string $targetType, array $targetIds): string
    {
        if ($targetType === 'all') { return db_trans('all_members'); }
        $names = match ($targetType) {
            'kanda' => Kanda::whereIn('id', $targetIds)->pluck('name')->all(),
            'jumuiya' => Jumuiya::whereIn('id', $targetIds)->pluck('name')->all(),
            'familia' => Familia::whereIn('id', $targetIds)->pluck('name')->all(),
            'member', 'custom' => Member::whereIn('id', $targetIds)->get()->map(fn($m) => $m->full_name)->all(),
            default => [],
        };
        return ucfirst($targetType).': '.implode(', ', array_filter($names));
    }
}
