<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\CommunicationCampaign;
use App\Models\CommunicationLog;
use App\Models\CommunicationMessage;
use Illuminate\Http\Request;

class CommunicationLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->can('communication.view_logs'), 403);

        $query = CommunicationMessage::query()->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $request->string('delivery_status'));
        }

        if ($request->filled('phone')) {
            $query->where('recipient_phone_normalized', 'like', '%' . $request->string('phone') . '%');
        }

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->integer('campaign_id'));
        }

        $messages = $query->paginate(20)->withQueryString();
        $campaigns = CommunicationCampaign::query()->latest()->take(50)->get(['id', 'title']);

        $auditLogs = class_exists(CommunicationLog::class)
            ? CommunicationLog::query()->latest()->take(20)->get()
            : collect();

        return view('admin.communication.logs.index', compact('messages', 'campaigns', 'auditLogs'));
    }
}
