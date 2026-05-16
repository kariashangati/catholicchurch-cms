<?php

namespace App\Http\Controllers\Admin\ContactMessages;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\DB;

class ContactMessageDashboardController extends Controller
{
    public function index()
    {
        $total = ContactMessage::count();
        $new = ContactMessage::where('status', ContactMessage::STATUS_NEW)->count();
        $answered = ContactMessage::where('status', ContactMessage::STATUS_ANSWERED)->count();
        $closed = ContactMessage::where('status', ContactMessage::STATUS_CLOSED)->count();
        $smsSent = ContactMessage::where('last_sms_status', 'imetumwa')->count();

        $monthly = ContactMessage::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as total")
            ->whereNotNull('created_at')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $statusRows = ContactMessage::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $latest = ContactMessage::with(['reason', 'kanda', 'jumuiya', 'assignee'])->latest()->limit(8)->get();
        $pending = ContactMessage::with(['reason', 'kanda', 'jumuiya'])->open()->latest()->limit(8)->get();

        return view('admin.contact-messages.dashboard', [
            'kpis' => [
                ['title' => db_trans('contact_total_messages'), 'value' => $total, 'icon' => 'fas fa-envelope-open-text', 'tone' => 'primary'],
                ['title' => db_trans('contact_new_messages'), 'value' => $new, 'icon' => 'fas fa-bell', 'tone' => 'warning'],
                ['title' => db_trans('contact_answered_messages'), 'value' => $answered, 'icon' => 'fas fa-reply', 'tone' => 'success'],
                ['title' => db_trans('contact_sms_sent'), 'value' => $smsSent, 'icon' => 'fas fa-sms', 'tone' => 'info'],
            ],
            'monthlyChart' => [
                'labels' => $monthly->pluck('period')->values(),
                'values' => $monthly->pluck('total')->values(),
            ],
            'statusChart' => [
                'labels' => $statusRows->map(fn ($row) => $this->statusLabel($row->status))->values(),
                'values' => $statusRows->pluck('total')->values(),
            ],
            'latest' => $latest,
            'pending' => $pending,
            'closed' => $closed,
        ]);
    }

    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            ContactMessage::STATUS_NEW => db_trans('contact_status_new'),
            ContactMessage::STATUS_READ => db_trans('contact_status_read'),
            ContactMessage::STATUS_ANSWERED => db_trans('contact_status_answered'),
            ContactMessage::STATUS_CLOSED => db_trans('contact_status_closed'),
            default => (string) $status,
        };
    }
}
