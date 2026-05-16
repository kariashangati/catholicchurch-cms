<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\AgeGroup;
use App\Models\CommunicationBalanceSnapshot;
use App\Models\CommunicationCampaign;
use App\Models\CommunicationMessage;
use App\Models\CommunicationTemplate;
use App\Models\ContributionType;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Services\Communication\CommunicationCampaignDispatcherService;
use App\Services\Communication\CommunicationDashboardService;
use App\Services\Communication\CommunicationSmsBalanceService;
use App\Services\Communication\CommunicationSmsSettingsService;
use App\Services\Communication\SmsTemplateRenderService;
use App\Services\Communication\Support\RecipientResolverService;
use App\Services\Communication\Support\SmsSegmentCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SmsMessageController extends Controller
{
    public function __construct(
        protected CommunicationSmsSettingsService $smsSettings,
        protected SmsTemplateRenderService $templateRenderer,
        protected CommunicationSmsBalanceService $balanceService,
    ) {
    }

    public function index(CommunicationDashboardService $dashboardService): View
    {
        $campaigns = CommunicationCampaign::query()
            ->where('channel', 'sms')
            ->latest()
            ->paginate(5);

        return view('admin.communication.sms.index', array_merge($dashboardService->getOverview(), [
            'campaigns' => $campaigns,
        ]));
    }

    public function create(): View
    {
        return view('admin.communication.sms.create', $this->formOptions());
    }

    public function preview(
        Request $request,
        RecipientResolverService $resolver,
        SmsSegmentCalculatorService $segmentService
    ): JsonResponse {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'audience_type' => ['required', 'string'],
        ]);

        $recipients = $resolver->resolve($this->normalizedPayload($request));
        $segmentData = $segmentService->analyze($request->string('message')->toString());
        $perMessageSegments = max(1, (int) ($segmentData['segments'] ?? 1));
        $totalSegments = $recipients->count() * $perMessageSegments;
        $unitPrice = $this->smsSettings->smsUnitPrice();
        $balance = $this->balanceService->currentBalance();
        $estimatedCost = $totalSegments * $unitPrice;

        return response()->json([
            'count' => $recipients->count(),
            'segments' => $perMessageSegments,
            'per_message' => $perMessageSegments,
            'total_segments' => $totalSegments,
            'encoding' => $segmentData['encoding'] ?? 'GSM-7',
            'length' => $segmentData['length'] ?? 0,
            'single_limit' => $segmentData['single_limit'] ?? 160,
            'multi_limit' => $segmentData['multi_limit'] ?? 153,
            'unit_price' => $unitPrice,
            'currency' => $this->smsSettings->currency(),
            'estimated_cost' => $estimatedCost,
            'balance' => $balance,
            'enough_balance' => $recipients->isNotEmpty() && $estimatedCost > 0 && $balance >= $estimatedCost,
        ]);
    }

    public function store(
        Request $request,
        RecipientResolverService $resolver,
        SmsSegmentCalculatorService $segmentService,
        CommunicationCampaignDispatcherService $dispatcher
    ): RedirectResponse {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'audience_type' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'send_mode' => ['nullable', 'in:now,later'],
            'scheduled_at' => ['nullable', 'date'],
            'template_id' => ['nullable', 'integer'],
            'member_id' => ['nullable', 'integer'],
            'member_ids' => ['nullable', 'array'],
            'familia_id' => ['nullable', 'integer'],
            'familia_ids' => ['nullable', 'array'],
            'jumuiya_id' => ['nullable', 'integer'],
            'jumuiya_ids' => ['nullable', 'array'],
            'kanda_id' => ['nullable', 'integer'],
            'kanda_ids' => ['nullable', 'array'],
            'filters' => ['nullable', 'array'],
            'year' => ['nullable', 'integer'],
            'month' => ['nullable', 'integer'],
            'contribution_type_id' => ['nullable', 'integer'],
        ]);

        $messageBody = $request->string('message')->toString();
        $payload = $this->normalizedPayload($request);
        $recipients = $resolver->resolve($payload);

        if ($recipients->isEmpty()) {
            return back()->withInput()->with('error', db_trans('communication.no_recipients_found'));
        }

        $segmentData = $segmentService->analyze($messageBody);
        $perMessageSegments = max(1, (int) ($segmentData['segments'] ?? 1));
        $isScheduled = $request->input('send_mode') === 'later' && filled($request->input('scheduled_at'));
        $unitPrice = $this->smsSettings->smsUnitPrice();
        $totalSegments = $recipients->count() * $perMessageSegments;
        $estimatedCost = $totalSegments * $unitPrice;

        if (! $this->balanceService->hasEnough($estimatedCost)) {
            return back()->withInput()->with('error', db_trans('sms_balance_finished_add_more'));
        }

        $campaign = null;

        DB::transaction(function () use ($request, $messageBody, $recipients, $segmentData, $perMessageSegments, $isScheduled, $payload, $estimatedCost, $totalSegments, &$campaign) {
            $campaign = CommunicationCampaign::create([
                'title' => $request->input('title') ?: 'SMS - ' . now()->format('Y-m-d H:i:s'),
                'type' => $recipients->count() === 1 ? 'manual_single' : 'manual_bulk',
                'channel' => 'sms',
                'template_id' => $request->input('template_id') ?: null,
                'audience_type' => $request->input('audience_type'),
                'audience_filters' => $payload,
                'message_body' => $messageBody,
                'status' => $isScheduled ? 'scheduled' : 'approved',
                'scheduled_at' => $isScheduled ? $request->date('scheduled_at') : null,
                'created_by' => auth()->id(),
                'total_recipients' => $recipients->count(),
                'valid_recipients' => $recipients->count(),
                'invalid_recipients' => 0,
                'total_messages' => $recipients->count(),
                'total_segments' => $totalSegments,
                'estimated_cost_units' => $estimatedCost,
                'actual_cost_units' => 0,
            ]);

            foreach ($recipients as $recipient) {
                $renderedBody = $this->renderRecipientMessage($messageBody, $recipient);

                CommunicationMessage::create([
                    'campaign_id' => $campaign->id,
                    'template_id' => $request->input('template_id') ?: null,
                    'member_id' => $recipient['member_id'] ?? null,
                    'familia_id' => $recipient['familia_id'] ?? null,
                    'jumuiya_id' => $recipient['jumuiya_id'] ?? null,
                    'kanda_id' => $recipient['kanda_id'] ?? null,
                    'recipient_name' => $recipient['name'] ?? $recipient['recipient_name'] ?? null,
                    'recipient_phone' => $recipient['phone'] ?? null,
                    'recipient_phone_normalized' => $recipient['phone_normalized'] ?? ($recipient['phone'] ?? null),
                    'recipient_type' => $recipient['recipient_type'] ?? 'member',
                    'locale' => $recipient['locale'] ?? app()->getLocale(),
                    'message_body' => $renderedBody,
                    'segment_count' => $perMessageSegments,
                    'status' => 'pending',
                    'delivery_status' => 'pending',
                    'provider' => 'beem',
                ]);
            }
        });

        if (! $isScheduled && $campaign) {
            $dispatcher->dispatch($campaign);
        }

        return redirect()
            ->route('admin.communication.sms.index')
            ->with('success', db_trans($isScheduled ? 'communication.sms_scheduled' : 'communication.sms_created'));
    }

    public function template(CommunicationTemplate $template): JsonResponse
    {
        return response()->json([
            'body' => $template->body,
            'variables' => $template->variables ?? [],
        ]);
    }

    protected function normalizedPayload(Request $request): array
    {
        $payload = $request->all();
        $filters = (array) ($payload['filters'] ?? []);

        foreach (['kanda_id', 'jumuiya_id', 'familia_id', 'age_group_id', 'contribution_type_id', 'year', 'month'] as $key) {
            if (array_key_exists($key, $payload) && filled($payload[$key])) {
                $filters[$key] = $payload[$key];
            }
        }

        $payload['filters'] = array_filter($filters, fn ($value) => $value !== null && $value !== '');

        return $payload;
    }

    protected function renderRecipientMessage(string $body, array $recipient): string
    {
        $replacements = [
            '{{ member_name }}' => (string) ($recipient['name'] ?? $recipient['recipient_name'] ?? ''),
            '{{ phone }}' => (string) ($recipient['phone'] ?? ''),
            '{{ date }}' => now()->format('d/m/Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $body);
    }

    protected function formOptions(): array
    {
        return [
            'members' => Member::query()->with('familia.jumuiya.kanda')->orderBy('first_name')->get(),
            'familias' => Familia::query()->with('jumuiya.kanda')->orderBy('name')->get(),
            'jumuiyas' => Jumuiya::query()->with('kanda')->orderBy('name')->get(),
            'kandas' => Kanda::query()->orderBy('name')->get(),
            'ageGroups' => class_exists(AgeGroup::class) ? AgeGroup::query()->orderBy('name')->get() : collect(),
            'contributionTypes' => ContributionType::query()->where('is_active', true)->orderBy('name')->get(),
            'templates' => CommunicationTemplate::query()->where('channel', 'sms')->where('status', 'active')->orderBy('name')->get(),
            'smsSettings' => $this->smsSettings->current(),
            'latestBalance' => CommunicationBalanceSnapshot::query()->latest('fetched_at')->first(),
        ];
    }
}
