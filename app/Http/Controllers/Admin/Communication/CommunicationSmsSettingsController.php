<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Services\Communication\CommunicationSmsSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunicationSmsSettingsController extends Controller
{
    public function __construct(protected CommunicationSmsSettingsService $settingsService)
    {
    }

    public function edit(): View
    {
        return view('admin.communication.settings.sms', [
            'settings' => $this->settingsService->current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', 'string', 'max:30'],
            'sender_id' => ['nullable', 'string', 'max:50'],
            'segment_length' => ['required', 'integer', 'min:1', 'max:500'],
            'unicode_segment_length' => ['required', 'integer', 'min:1', 'max:500'],
            'sms_unit_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->settingsService->save($data, $request->user()?->id);

        return back()->with('success', db_trans('sms_settings_saved_successfully'));
    }
}
