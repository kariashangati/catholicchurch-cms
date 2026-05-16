<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MassScheduleAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'mass_schedule_id',
        'role_name',
        'assignable_type',
        'assignable_id',
        'notes',
    ];

    public function massSchedule()
    {
        return $this->belongsTo(MassSchedule::class);
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignableLabel(): string
    {
        $target = $this->assignable;

        if (! $target) {
            return '—';
        }

        foreach (['full_name', 'name', 'title'] as $field) {
            if (isset($target->{$field}) && filled($target->{$field})) {
                return (string) $target->{$field};
            }
        }

        if (method_exists($target, 'getAttribute')) {
            foreach (['jina_kamili', 'aina_ya_huduma'] as $field) {
                if (filled($target->getAttribute($field))) {
                    return (string) $target->getAttribute($field);
                }
            }
        }

        return class_basename($this->assignable_type) . ' #' . $this->assignable_id;
    }
}
