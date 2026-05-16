<?php
namespace App\Services\Sermons;
use App\Models\SermonRequest;
use App\Models\SermonRequestLog;

class SermonStatusService
{
    public function changeRequestStatus(SermonRequest $request, string $status, ?string $note = null): SermonRequest
    {
        $old = $request->status;
        $request->forceFill(['status' => $status, 'admin_note' => $note ?: $request->admin_note])->save();
        SermonRequestLog::create([
            'sermon_request_id' => $request->id,
            'old_status' => $old,
            'new_status' => $status,
            'note' => $note,
            'changed_by' => auth()->id(),
        ]);
        return $request->fresh();
    }
}
