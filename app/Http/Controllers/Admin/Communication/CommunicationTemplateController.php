<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreCommunicationTemplateRequest;
use App\Http\Requests\Communication\UpdateCommunicationTemplateRequest;
use App\Models\CommunicationTemplate;
use App\Services\Communication\SmsTemplateRenderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationTemplateController extends Controller
{
    public function __construct(protected SmsTemplateRenderService $renderer)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CommunicationTemplate::class);

        $templates = CommunicationTemplate::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('locale'), fn ($q) => $q->where('locale', $request->string('locale')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->string('search'));
                $q->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'all' => CommunicationTemplate::count(),
            'active' => CommunicationTemplate::where('status', CommunicationTemplate::STATUS_ACTIVE)->count(),
            'draft' => CommunicationTemplate::where('status', CommunicationTemplate::STATUS_DRAFT)->count(),
            'sw' => CommunicationTemplate::where('locale', 'sw')->count(),
        ];

        return view('admin.communication.templates.index', compact('templates', 'stats'));
    }

    public function create(): View
    {
        $this->authorize('create', CommunicationTemplate::class);

        return view('admin.communication.templates.create', [
            'template' => new CommunicationTemplate([
                'channel' => CommunicationTemplate::CHANNEL_SMS,
                'locale' => 'sw',
                'status' => CommunicationTemplate::STATUS_DRAFT,
            ]),
            'detectedVariables' => [],
        ]);
    }

    public function store(StoreCommunicationTemplateRequest $request): RedirectResponse
    {
        $payload = $this->preparePayload($request->validated());
        $payload['created_by'] = auth()->id();
        $payload['updated_by'] = auth()->id();

        CommunicationTemplate::create($payload);

        return redirect()
            ->route('admin.communication.templates.index')
            ->with('success', db_trans('communication.templates.created_success'));
    }

    public function show(CommunicationTemplate $template): View
    {
        $this->authorize('view', $template);

        $previewData = $this->defaultPreviewData();
        $preview = $this->renderer->render($template, $previewData);

        return view('admin.communication.templates.show', compact('template', 'previewData', 'preview'));
    }

    public function edit(CommunicationTemplate $template): View
    {
        $this->authorize('update', $template);

        $detectedVariables = $this->renderer->extractVariables($template->body . ' ' . ($template->subject ?? ''));

        return view('admin.communication.templates.edit', compact('template', 'detectedVariables'));
    }

    public function update(UpdateCommunicationTemplateRequest $request, CommunicationTemplate $template): RedirectResponse
    {
        $payload = $this->preparePayload($request->validated());
        $payload['updated_by'] = auth()->id();

        $template->update($payload);

        return redirect()
            ->route('admin.communication.templates.index')
            ->with('success', db_trans('communication.templates.updated_success'));
    }

    public function destroy(CommunicationTemplate $template): RedirectResponse
    {
        $this->authorize('delete', $template);

        $template->delete();

        return redirect()
            ->route('admin.communication.templates.index')
            ->with('success', db_trans('communication.templates.deleted_success'));
    }

    public function preview(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('communication.manage_templates'), 403);

        $request->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
            'variables' => ['nullable', 'array'],
        ]);

        $template = new CommunicationTemplate([
            'subject' => $request->input('subject'),
            'body' => $request->input('body'),
            'variables' => array_values(array_filter((array) $request->input('variables', []))),
        ]);

        $preview = $this->renderer->render($template, $this->defaultPreviewData());

        return response()->json($preview);
    }

    protected function preparePayload(array $validated): array
    {
        $validated['code'] = $validated['code'] ?: $this->renderer->normalizeCode($validated['name']);
        $detectedVariables = $this->renderer->extractVariables(($validated['subject'] ?? '') . ' ' . $validated['body']);
        $manualVariables = array_values(array_filter($validated['variables'] ?? []));

        $validated['variables'] = array_values(array_unique(array_merge($manualVariables, $detectedVariables)));

        return $validated;
    }

    protected function defaultPreviewData(): array
    {
        return [
            'member_name' => 'Maria Anna',
            'first_name' => 'Maria',
            'familia_name' => 'Mt. Yosefu',
            'jumuiya_name' => 'Mt. Anna',
            'kanda_name' => 'Kanda A',
            'amount' => '15,000',
            'month_name' => now()->translatedFormat('F Y'),
            'parish_name' => config('app.name', 'Parokia'),
            'date' => now()->format('d/m/Y'),
            'phone' => '255712345678',
        ];
    }
}
