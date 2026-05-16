<?php

namespace App\Http\Controllers\Admin\ContactMessages;

use App\Http\Controllers\Controller;
use App\Models\ContactReason;
use Illuminate\Http\Request;

class ContactReasonController extends Controller
{
    public function index()
    {
        return view('admin.contact-messages.reasons.index', [
            'reasons' => ContactReason::orderBy('display_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        ContactReason::create($this->validated($request));
        return back()->with('success', db_trans('contact_reason_saved_successfully'));
    }

    public function update(Request $request, ContactReason $reason)
    {
        $reason->update($this->validated($request));
        return back()->with('success', db_trans('contact_reason_saved_successfully'));
    }

    public function toggle(ContactReason $reason)
    {
        $reason->forceFill(['is_active' => ! $reason->is_active])->save();
        return back()->with('success', db_trans('contact_reason_saved_successfully'));
    }

    public function destroy(ContactReason $reason)
    {
        if ($reason->contactMessages()->exists()) {
            return back()->with('error', db_trans('contact_reason_has_messages'));
        }
        $reason->delete();
        return back()->with('success', db_trans('contact_reason_deleted_successfully'));
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
