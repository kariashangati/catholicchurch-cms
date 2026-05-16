<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\UpdateCommunicationControlSettingsRequest;
use App\Services\Communication\CommunicationControlSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CommunicationControlSettingsController extends Controller
{
    public function __construct(
        private readonly CommunicationControlSettingsService $settingsService,
    ) {}

    public function edit(): View
    {
        $settings = $this->settingsService->all();

        return view('admin.communication.settings.controls', compact('settings'));
    }

    public function update(UpdateCommunicationControlSettingsRequest $request): RedirectResponse
    {
        $this->settingsService->update($request->validated());

        return back()->with('success', db_trans('communication.controls.updated_success'));
    }
}
