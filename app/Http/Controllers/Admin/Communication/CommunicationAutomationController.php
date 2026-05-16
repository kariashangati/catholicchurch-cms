<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreCommunicationAutomationRequest;
use App\Http\Requests\Communication\UpdateCommunicationAutomationRequest;
use App\Models\CommunicationAutomation;
use App\Models\CommunicationTemplate;
use App\Services\Communication\CommunicationAutomationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CommunicationAutomationController extends Controller
{
    public function __construct(private readonly CommunicationAutomationService $automationService)
    {
        $this->authorizeResource(CommunicationAutomation::class, 'automation');
    }

    public function index(): View
    {
        $automations = CommunicationAutomation::query()
            ->with('template:id,name,code,locale,status')
            ->latest()
            ->paginate(20);

        $eventOptions = $this->automationService->eventOptions();

        return view('admin.communication.automations.index', compact('automations', 'eventOptions'));
    }

    public function create(): View
    {
        $templates = CommunicationTemplate::query()
            ->where('channel', 'sms')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'locale']);

        $eventOptions = $this->automationService->eventOptions();
        $triggerModes = $this->automationService->triggerModes();
        $audienceTypes = $this->automationService->audienceTypes();

        return view('admin.communication.automations.create', compact('templates', 'eventOptions', 'triggerModes', 'audienceTypes'));
    }

    public function store(StoreCommunicationAutomationRequest $request): RedirectResponse
    {
        $automation = $this->automationService->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.communication.automations.edit', $automation)
            ->with('success', db_trans('communication.automations.created_successfully'));
    }

    public function edit(CommunicationAutomation $automation): View
    {
        $templates = CommunicationTemplate::query()
            ->where('channel', 'sms')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'locale']);

        $eventOptions = $this->automationService->eventOptions();
        $triggerModes = $this->automationService->triggerModes();
        $audienceTypes = $this->automationService->audienceTypes();
        $conditionSchema = $this->automationService->conditionSchema($automation->event_key);

        return view('admin.communication.automations.edit', compact(
            'automation',
            'templates',
            'eventOptions',
            'triggerModes',
            'audienceTypes',
            'conditionSchema'
        ));
    }

    public function update(UpdateCommunicationAutomationRequest $request, CommunicationAutomation $automation): RedirectResponse
    {
        $this->automationService->update($automation, $request->validated(), $request->user());

        return redirect()
            ->route('admin.communication.automations.edit', $automation)
            ->with('success', db_trans('communication.automations.updated_successfully'));
    }

    public function destroy(CommunicationAutomation $automation): RedirectResponse
    {
        $automation->delete();

        return redirect()
            ->route('admin.communication.automations.index')
            ->with('success', db_trans('communication.automations.deleted_successfully'));
    }

    public function toggle(CommunicationAutomation $automation): RedirectResponse
    {
        $automation->update(['is_enabled' => ! $automation->is_enabled]);

        return back()->with('success', db_trans('communication.automations.toggled_successfully'));
    }

    public function schema(string $eventKey): JsonResponse
    {
        return response()->json([
            'schema' => $this->automationService->conditionSchema($eventKey),
        ]);
    }
}
