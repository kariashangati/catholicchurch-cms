<?php
namespace App\Http\Controllers\Admin\Sermons;
use App\Http\Controllers\Controller;
use App\Models\Sermon;
use App\Models\SermonRecipient;
use App\Models\SermonRequest;
use App\Models\SermonView;
use Illuminate\Support\Facades\DB;

class SermonDashboardController extends Controller
{
    public function index()
    {
        $kpis = [
            ['title'=>db_trans('total_sermons'),'value'=>Sermon::count(),'icon'=>'fas fa-book-bible','tone'=>'primary'],
            ['title'=>db_trans('published_sermons'),'value'=>Sermon::where('status', Sermon::STATUS_PUBLISHED)->count(),'icon'=>'fas fa-bullhorn','tone'=>'success'],
            ['title'=>db_trans('new_sermon_requests'),'value'=>SermonRequest::where('status', SermonRequest::STATUS_RECEIVED)->count(),'icon'=>'fas fa-envelope-open-text','tone'=>'warning'],
            ['title'=>db_trans('sermon_views'),'value'=>SermonView::count(),'icon'=>'fas fa-eye','tone'=>'info'],
        ];

        $monthly = Sermon::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')->orderBy('month')->get()
            ->map(fn($r)=>['label'=>$r->month,'value'=>(int)$r->total])->values();

        $requestStatus = SermonRequest::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->get()
            ->map(fn($r)=>['label'=>db_trans('sermon_request_status_'.$r->status),'value'=>(int)$r->total])->values();

        return view('admin.sermons.dashboard', [
            'kpis'=>$kpis,
            'monthly'=>$monthly,
            'requestStatus'=>$requestStatus,
            'recentSermons'=>Sermon::latest()->withCount(['recipients','views'])->limit(8)->get(),
            'recentRequests'=>SermonRequest::latest()->limit(8)->get(),
            'recentRecipients'=>SermonRecipient::with(['sermon','token'])->latest()->limit(8)->get(),
        ]);
    }
}
