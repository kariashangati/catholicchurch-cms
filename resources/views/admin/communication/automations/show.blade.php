@extends('layouts.admin')

@section('content')
<div class="container-fluid cc-wrap">
    <div class="cc-hero">
        <div>
            <div class="cc-hero-kicker">{{ db_trans('communication.automations') }}</div>
            <h1 class="cc-hero-title">{{ $automation->name }}</h1>
            <p class="cc-hero-text">{{ $automation->event_key }} · {{ $triggerModeLabel }} · {{ $audienceTypeLabel }}</p>
        </div>
        <div class="cc-hero-actions d-flex gap-2">
            <a href="{{ route('admin.communication.automations.edit', $automation) }}" class="btn btn-light cc-hero-btn"><i class="bi bi-pencil-square"></i> Edit</a>
            <a href="{{ route('admin.communication.automations.index') }}" class="btn btn-outline-light cc-hero-btn">Back</a>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="cc-panel">
                <div class="cc-section-title">Automation details</div>
                <div class="cc-detail-grid">
                    <div><span>Code</span><strong>{{ $automation->code }}</strong></div>
                    <div><span>Channel</span><strong>{{ strtoupper($automation->channel) }}</strong></div>
                    <div><span>Event</span><strong>{{ $eventLabel }}</strong></div>
                    <div><span>Audience</span><strong>{{ $audienceTypeLabel }}</strong></div>
                    <div><span>Trigger mode</span><strong>{{ $triggerModeLabel }}</strong></div>
                    <div><span>Delay</span><strong>{{ $automation->delay_minutes ? $automation->delay_minutes . ' min' : 'None' }}</strong></div>
                    <div><span>Status</span><strong>{{ $automation->is_enabled ? 'Enabled' : 'Disabled' }}</strong></div>
                    <div><span>Template</span><strong>{{ $automation->template?->name ?? 'No template selected' }}</strong></div>
                </div>

                <div class="cc-section-title mt-4">Conditions</div>
                @if(!empty($automation->conditions))
                    <div class="cc-condition-list">
                        @foreach($automation->conditions as $condition)
                            <div class="cc-condition-pill">
                                <strong>{{ $condition['field'] ?? 'field' }}</strong>
                                <span>{{ $condition['operator'] ?? '=' }}</span>
                                <em>{{ is_scalar($condition['value'] ?? null) ? $condition['value'] : json_encode($condition['value'] ?? null) }}</em>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No extra conditions configured.</p>
                @endif

                @if($automation->notes)
                    <div class="cc-section-title mt-4">Notes</div>
                    <div class="cc-notes-box">{{ $automation->notes }}</div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="cc-panel cc-side-stack">
                <div class="cc-side-box">
                    <div class="cc-side-box-title">Protection rules</div>
                    <ul class="cc-plain-list mb-0">
                        <li>{{ $automation->respect_preferences ? 'Respects member preferences' : 'Ignores member preferences' }}</li>
                        <li>{{ $automation->respect_quiet_hours ? 'Quiet hours enabled' : 'Quiet hours disabled' }}</li>
                        <li>{{ $automation->send_once_per_entity ? 'Send once per entity enabled' : 'No send-once protection' }}</li>
                    </ul>
                </div>
                <div class="cc-side-box">
                    <div class="cc-side-box-title">Audit</div>
                    <ul class="cc-plain-list mb-0">
                        <li>Created by: {{ $automation->creator?->name ?? 'System' }}</li>
                        <li>Updated by: {{ $automation->updater?->name ?? 'System' }}</li>
                        <li>Created: {{ optional($automation->created_at)->format('d M Y H:i') }}</li>
                        <li>Updated: {{ optional($automation->updated_at)->format('d M Y H:i') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.cc-wrap{padding-bottom:2rem}.cc-hero{display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:1.5rem;border-radius:28px;background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 48%,#a855f7 100%);color:#fff;box-shadow:0 24px 55px rgba(79,70,229,.25)}.cc-hero-kicker{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;opacity:.9}.cc-hero-title{font-size:2rem;font-weight:800;margin:.35rem 0}.cc-hero-text{max-width:760px;opacity:.92;margin:0}.cc-hero-btn{border-radius:16px;padding:.9rem 1.15rem;font-weight:700}.cc-panel{background:#fff;border-radius:26px;padding:1.35rem;box-shadow:0 18px 55px rgba(15,23,42,.08)}.cc-section-title{font-weight:800;font-size:1rem;margin-bottom:.8rem}.cc-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.95rem}.cc-detail-grid div{padding:1rem;border:1px solid #eef2ff;border-radius:18px;background:#fafaff}.cc-detail-grid span{display:block;color:#64748b;font-size:.82rem;margin-bottom:.25rem}.cc-detail-grid strong{font-size:1rem}.cc-condition-list{display:flex;flex-wrap:wrap;gap:.65rem}.cc-condition-pill{display:inline-flex;align-items:center;gap:.45rem;padding:.65rem .85rem;border-radius:999px;background:#f5f3ff;color:#5b21b6;font-weight:700}.cc-notes-box{padding:1rem 1.1rem;border-radius:18px;background:#f8fafc;border:1px solid #e2e8f0;color:#334155}.cc-side-stack{display:flex;flex-direction:column;gap:1rem}.cc-side-box{padding:1rem;border-radius:20px;background:linear-gradient(180deg,#faf5ff 0%,#fff 100%);border:1px solid #ede9fe}.cc-side-box-title{font-weight:800;margin-bottom:.55rem}.cc-plain-list{padding-left:1rem;color:#475569}.cc-plain-list li+li{margin-top:.45rem}@media(max-width:991px){.cc-hero{flex-direction:column;align-items:flex-start}.cc-detail-grid{grid-template-columns:1fr}}</style>
@endpush
