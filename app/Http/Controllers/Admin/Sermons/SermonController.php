<?php
namespace App\Http\Controllers\Admin\Sermons;
use App\Http\Controllers\Controller;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\Sermon;
use App\Models\SermonRequest;
use App\Services\Sermons\SermonAudienceService;
use App\Services\Sermons\SermonSmsService;
use App\Services\Sermons\SermonTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SermonController extends Controller
{
    public function index()
    {
        return view('admin.sermons.index', [
            'sermons'=>Sermon::withCount(['recipients','views'])->latest()->get(),
        ]);
    }

    public function create()
    {
        return view('admin.sermons.form', $this->formData(new Sermon(['status'=>Sermon::STATUS_DRAFT,'is_active'=>true])) + ['mode'=>'create']);
    }

    public function store(Request $request, SermonAudienceService $audience, SermonTokenService $tokens)
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;
        $data['body_html'] = $this->cleanHtml($data['body_html'] ?? '');
        $data = $this->handleUploads($request, $data);
        $sermon = Sermon::create($data);
        $audience->syncTargetsAndRecipients($sermon, $request->all());
        $tokens->ensureTokens($sermon, $request->integer('token_days_valid') ?: null);
        return redirect()->route('sermons.edit', $sermon)->with('success', db_trans('sermon_saved_successfully'));
    }

    public function edit(Sermon $sermon)
    {
        $sermon->load(['targets','recipients.token']);
        return view('admin.sermons.form', $this->formData($sermon) + ['mode'=>'edit']);
    }

    public function update(Request $request, Sermon $sermon, SermonAudienceService $audience, SermonTokenService $tokens)
    {
        $data = $this->validated($request, $sermon->id);
        $data['updated_by'] = $request->user()->id;
        $data['body_html'] = $this->cleanHtml($data['body_html'] ?? '');
        $data = $this->handleUploads($request, $data, $sermon);
        $sermon->update($data);
        if ($request->boolean('resync_recipients')) {
            $audience->syncTargetsAndRecipients($sermon, $request->all());
            $tokens->ensureTokens($sermon, $request->integer('token_days_valid') ?: null);
        }
        return back()->with('success', db_trans('sermon_saved_successfully'));
    }

    public function publish(Sermon $sermon)
    {
        $sermon->forceFill(['status'=>Sermon::STATUS_PUBLISHED,'is_active'=>true,'published_at'=>$sermon->published_at ?: now()])->save();
        return back()->with('success', db_trans('sermon_published_successfully'));
    }

    public function deactivate(Sermon $sermon)
    {
        $sermon->forceFill(['status'=>Sermon::STATUS_INACTIVE,'is_active'=>false])->save();
        $sermon->tokens()->update(['is_active'=>false]);
        return back()->with('success', db_trans('sermon_deactivated_successfully'));
    }

    public function sendLinks(Sermon $sermon, SermonTokenService $tokens, SermonSmsService $sms)
    {
        $tokens->ensureTokens($sermon);
        $sent = 0; $failed = 0;
        $sermon->recipients()->with(['sermon','token'])->chunkById(50, function ($recipients) use ($sms, &$sent, &$failed) {
            foreach ($recipients as $recipient) { $sms->sendRecipientLink($recipient) ? $sent++ : $failed++; }
        });
        return back()->with('success', db_trans('sermon_sms_send_finished')." {$sent}/".($sent+$failed));
    }

    public function destroy(Sermon $sermon)
    {
        $sermon->delete();
        return redirect()->route('sermons.index')->with('success', db_trans('sermon_deleted_successfully'));
    }

    protected function formData(Sermon $sermon): array
    {
        return [
            'sermon'=>$sermon,
            'statuses'=>Sermon::statuses(),
            'kandas'=>Kanda::orderBy('name')->get(),
            'jumuiyas'=>Jumuiya::orderBy('name')->get(),
            'familias'=>Familia::orderBy('name')->get(),
            'members'=>Member::where('is_active', true)->orderBy('first_name')->limit(1000)->get(),
            'requests'=>SermonRequest::whereIn('status', [SermonRequest::STATUS_RECEIVED, SermonRequest::STATUS_PREPARING])->latest()->get(),
        ];
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'=>['required','string','max:180'],
            'summary'=>['nullable','string','max:500'],
            'body_html'=>['nullable','string'],
            'cover_image'=>['nullable','image','max:4096'],
            'thumbnail'=>['nullable','image','max:4096'],
            'video_source'=>['nullable','in:none,upload,url'],
            'video_file'=>['nullable','file','mimes:mp4,mov,webm','max:204800'],
            'video_url'=>['nullable','url','max:500'],
            'status'=>['required','in:'.implode(',', Sermon::statuses())],
            'is_active'=>['nullable','boolean'],
            'request_id'=>['nullable','exists:sermon_requests,id'],
        ]);
    }

    protected function handleUploads(Request $request, array $data, ?Sermon $sermon = null): array
    {
        if ($request->hasFile('cover_image')) { $data['cover_image_path'] = $request->file('cover_image')->store('sermons/covers','public'); }
        if ($request->hasFile('thumbnail')) { $data['thumbnail_path'] = $request->file('thumbnail')->store('sermons/thumbnails','public'); }
        if ($request->hasFile('video_file')) { $data['video_path'] = $request->file('video_file')->store('sermons/videos','public'); $data['video_source']='upload'; }
        $data['is_active'] = $request->boolean('is_active', true);
        return $data;
    }

    protected function cleanHtml(string $html): string
    {
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html) ?? '';
        $html = preg_replace('/on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        return $html;
    }
}
