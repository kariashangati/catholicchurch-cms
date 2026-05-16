<?php

namespace App\Http\Requests\Liturgy;

class UpdateMassScheduleRequest extends StoreMassScheduleRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('liturgy.mass-schedules.update') ?? false;
    }
}
