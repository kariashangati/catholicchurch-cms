<?php
namespace App\Http\Controllers\Admin\Sermons;
use App\Http\Controllers\Controller;
use App\Models\SermonRequest;
use App\Models\Sermon;
use App\Services\Sermons\SermonStatusService;
use Illuminate\Http\Request;

class SermonRequestController extends Controller
{
    public function index()
    {
        return view('admin.sermons.requests.index', [
            'requests'=>SermonRequest::with(['member','jumuiya','linkedSermon','assignee'])->latest()->get(),
            'statuses'=>SermonRequest::statuses(),
            'sermons'=>Sermon::where('status', Sermon::STATUS_PUBLISHED)->latest()->get(),
        ]);
    }
    public function changeStatus(Request $request, SermonRequest $sermonRequest, SermonStatusService $statusService)
    {
        $data = $request->validate(['status'=>['required','in:'.implode(',', SermonRequest::statuses())], 'note'=>['nullable','string','max:1000']]);
        $statusService->changeRequestStatus($sermonRequest, $data['status'], $data['note'] ?? null);
        return back()->with('success', db_trans('sermon_request_updated_successfully'));
    }
    public function respond(Request $request, SermonRequest $sermonRequest, SermonStatusService $statusService)
    {
        $data = $request->validate(['linked_sermon_id'=>['required','exists:sermons,id'], 'note'=>['nullable','string','max:1000']]);
        $sermonRequest->forceFill(['linked_sermon_id'=>$data['linked_sermon_id']])->save();
        $statusService->changeRequestStatus($sermonRequest, SermonRequest::STATUS_SENT, $data['note'] ?? null);
        return back()->with('success', db_trans('sermon_request_responded_successfully'));
    }
}
