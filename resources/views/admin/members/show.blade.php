@extends('layouts.admin')

@section('title', db_trans('member_details'))

@section('content')
    @php
        $locale = app()->getLocale();

        $mapLocalizedValue = function (?string $value, array $translations = []) use ($locale) {
            if (blank($value)) {
                return '—';
            }

            $normalized = strtolower(trim($value));

            if (isset($translations[$normalized])) {
                return $translations[$normalized][$locale] ?? $translations[$normalized]['en'] ?? ucfirst($value);
            }

            return ucfirst($value);
        };

        $genderLabel = $mapLocalizedValue($member->gender, [
            'male' => ['en' => 'Male', 'sw' => 'Mwanaume'],
            'female' => ['en' => 'Female', 'sw' => 'Mwanamke'],
        ]);

        $familyRoleLabel = $mapLocalizedValue($member->family_role, [
            'father' => ['en' => 'Father', 'sw' => 'Baba'],
            'mother' => ['en' => 'Mother', 'sw' => 'Mama'],
            'child' => ['en' => 'Child', 'sw' => 'Mtoto'],
            'other' => ['en' => 'Other', 'sw' => 'Nyingine'],
        ]);

        $marriageTypeLabel = $mapLocalizedValue($member->marriage_type, [
            'church' => ['en' => 'Church', 'sw' => 'Kanisani'],
            'civil' => ['en' => 'Civil', 'sw' => 'Serikali'],
            'traditional' => ['en' => 'Traditional', 'sw' => 'Kimila'],
            'customary' => ['en' => 'Customary', 'sw' => 'Kimila'],
            'other' => ['en' => 'Other', 'sw' => 'Nyingine'],
        ]);

        $memberInitials = collect([
            $member->first_name ? mb_substr($member->first_name, 0, 1) : null,
            $member->last_name ? mb_substr($member->last_name, 0, 1) : null,
        ])->filter()->implode('');

        $memberInitials = $memberInitials !== '' ? mb_strtoupper($memberInitials) : 'M';

        $profileStats = [
            [
                'label' => db_trans('gender'),
                'value' => $genderLabel,
                'icon' => 'fas fa-venus-mars',
            ],
            [
                'label' => db_trans('family_role'),
                'value' => $familyRoleLabel,
                'icon' => 'fas fa-people-roof',
            ],
            [
                'label' => db_trans('status'),
                'value' => $member->is_active ? db_trans('active') : db_trans('inactive'),
                'icon' => $member->is_active ? 'fas fa-circle-check' : 'fas fa-circle-pause',
            ],
            [
                'label' => db_trans('phone'),
                'value' => $member->phone ?: '—',
                'icon' => 'fas fa-phone',
            ],
        ];

        $sacramentProgress = collect([
            (bool) $member->is_baptized,
            (bool) $member->has_communion,
            (bool) $member->has_confirmation,
            (bool) $member->receives_eucharist,
            (bool) $member->is_married,
        ])->filter()->count();

        $sacramentCompletion = (int) round(($sacramentProgress / 5) * 100);

        $identityItems = [
            db_trans('member_code') => $member->member_code ?: '—',
            db_trans('date_of_birth') => optional($member->date_of_birth)->format('d M Y') ?: '—',
            db_trans('occupation') => $member->occupation ?: '—',
            db_trans('phone') => $member->phone ?: '—',
        ];

        $churchItems = [
            db_trans('familia') => $member->familia?->name ?: '—',
            db_trans('jumuiya') => $member->familia?->jumuiya?->name ?: '—',
            db_trans('kanda') => $member->familia?->jumuiya?->kanda?->name ?: '—',
            db_trans('status') => $member->is_active ? db_trans('active') : db_trans('inactive'),
        ];

        $sacramentItems = [
            db_trans('baptized') => $member->is_baptized ? db_trans('yes') : db_trans('no'),
            db_trans('communion') => $member->has_communion ? db_trans('yes') : db_trans('no'),
            db_trans('confirmation') => $member->has_confirmation ? db_trans('yes') : db_trans('no'),
            db_trans('eucharist') => $member->receives_eucharist ? db_trans('yes') : db_trans('no'),
            db_trans('married') => $member->is_married ? db_trans('yes') : db_trans('no'),
            db_trans('marriage_type') => $marriageTypeLabel,
            db_trans('baptism_certificate_number') => $member->baptism_certificate_number ?: '—',
            db_trans('marriage_certificate_number') => $member->marriage_certificate_number ?: '—',
            db_trans('baptism_parish') => $member->baptism_parish ?: '—',
            db_trans('baptism_diocese') => $member->baptism_diocese ?: '—',
        ];
    @endphp

    <div class="members-show-page">
        <div class="dashboard-hero members-show-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-xl-8">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 mb-3">
                        <span class="dashboard-hero-badge">{{ db_trans('member_profile') }}</span>
                        <span class="members-show-chip {{ $member->is_active ? 'is-active' : 'is-inactive' }}">
                            <i class="fas {{ $member->is_active ? 'fa-circle-check' : 'fa-circle-pause' }} me-2"></i>
                            {{ $member->is_active ? db_trans('active_member') : db_trans('inactive_member') }}
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="members-show-avatar">{{ $memberInitials }}</div>
                        <div>
                            <h1 class="dashboard-title mb-2">{{ $member->full_name }}</h1>
                            <p class="dashboard-subtitle mb-0">
                                {{ $member->member_code ?? '—' }} · {{ $member->familia?->jumuiya?->name ?? '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        @foreach($profileStats as $stat)
                            <div class="col-sm-6 col-xl-3">
                                <div class="members-show-mini-card">
                                    <div class="members-show-mini-icon">
                                        <i class="{{ $stat['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="members-show-mini-label">{{ $stat['label'] }}</div>
                                        <div class="members-show-mini-value">{{ $stat['value'] }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="members-show-hero-side">
                        <div class="members-show-quick-grid">
                            @can('members.view')
                                <a href="{{ route('pdf.members.profile', $member) }}" class="members-show-action-card">
                                    <span class="members-show-action-icon"><i class="fas fa-file-pdf"></i></span>
                                    <span>
                                        <strong>{{ db_trans('export_pdf') }}</strong>
                                    </span>
                                </a>
                            @endcan

                            @can('members.update')
                                <a href="{{ route('members.edit', $member) }}" class="members-show-action-card">
                                    <span class="members-show-action-icon"><i class="fas fa-pen"></i></span>
                                    <span>
                                        <strong>{{ db_trans('edit_member') }}</strong>
                                    </span>
                                </a>
                            @endcan

                            <a href="{{ route('members.index') }}" class="members-show-action-card">
                                <span class="members-show-action-icon"><i class="fas fa-users"></i></span>
                                <span>
                                    <strong>{{ db_trans('member_directory') }}</strong>
                                </span>
                            </a>

                            <a href="{{ url()->previous() }}" class="members-show-action-card">
                                <span class="members-show-action-icon"><i class="fas fa-arrow-left"></i></span>
                                <span>
                                    <strong>{{ db_trans('back') }}</strong>
                                </span>
                            </a>

                            <div class="members-show-action-card static-card">
                                <span class="members-show-action-icon"><i class="fas fa-chart-line"></i></span>
                                <span>
                                    <strong>{{ db_trans('sacrament_completion') }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-4">
                <div class="card dashboard-panel h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="members-show-section-heading mb-4">
                            <h5 class="mb-1">{{ db_trans('profile_overview') }}</h5>
                        </div>

                        <div class="members-show-summary-stack">
                            <div class="members-show-summary-card primary-card">
                                <span class="summary-label">{{ db_trans('member') }}</span>
                                <strong>{{ $member->full_name }}</strong>
                                <small>{{ $member->member_code ?? '—' }}</small>
                            </div>

                            <div class="members-show-summary-grid">
                                <div class="members-show-summary-card">
                                    <span class="summary-label">{{ db_trans('gender') }}</span>
                                    <strong>{{ $genderLabel }}</strong>
                                </div>
                                <div class="members-show-summary-card">
                                    <span class="summary-label">{{ db_trans('family_role') }}</span>
                                    <strong>{{ $familyRoleLabel }}</strong>
                                </div>
                                <div class="members-show-summary-card">
                                    <span class="summary-label">{{ db_trans('familia') }}</span>
                                    <strong>{{ $member->familia?->name ?? '—' }}</strong>
                                </div>
                                <div class="members-show-summary-card">
                                    <span class="summary-label">{{ db_trans('jumuiya') }}</span>
                                    <strong>{{ $member->familia?->jumuiya?->name ?? '—' }}</strong>
                                </div>
                            </div>
                        </div>

                        @if($member->notes)
                            <div class="members-show-note-box mt-4">
                                <div class="members-show-note-title">
                                    <i class="fas fa-note-sticky me-2"></i>{{ db_trans('notes') }}
                                </div>
                                <div class="text-muted">{{ $member->notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card dashboard-panel h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="members-show-section-heading mb-4">
                            <h5 class="mb-1">{{ db_trans('member_profile_details') }}</h5>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="members-show-detail-card h-100">
                                    <div class="members-show-card-title">
                                        <i class="fas fa-id-card"></i>
                                        {{ db_trans('personal_information') }}
                                    </div>
                                    <div class="members-show-detail-list">
                                        @foreach($identityItems as $label => $value)
                                            <div class="members-show-detail-row">
                                                <span>{{ $label }}</span>
                                                <strong>{{ $value }}</strong>
                                            </div>
                                        @endforeach
                                        <div class="members-show-detail-row">
                                            <span>{{ db_trans('gender') }}</span>
                                            <strong>{{ $genderLabel }}</strong>
                                        </div>
                                        <div class="members-show-detail-row">
                                            <span>{{ db_trans('family_role') }}</span>
                                            <strong>{{ $familyRoleLabel }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="members-show-detail-card h-100">
                                    <div class="members-show-card-title">
                                        <i class="fas fa-church"></i>
                                        {{ db_trans('church_membership') }}
                                    </div>
                                    <div class="members-show-detail-list">
                                        @foreach($churchItems as $label => $value)
                                            <div class="members-show-detail-row">
                                                <span>{{ $label }}</span>
                                                <strong>{{ $value }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card dashboard-panel h-100 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 p-4 pb-0">
                        <div class="members-show-section-heading">
                            <h5 class="mb-1">{{ db_trans('sacrament_journey') }}</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="members-show-progress-box mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold">{{ db_trans('sacrament_completion') }}</span>
                                <span class="members-show-progress-value">{{ $sacramentCompletion }}%</span>
                            </div>
                            <div class="progress members-show-progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ $sacramentCompletion }}%" aria-valuenow="{{ $sacramentCompletion }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="members-show-detail-list compact-list">
                            @foreach($sacramentItems as $label => $value)
                                <div class="members-show-detail-row">
                                    <span>{{ $label }}</span>
                                    <strong>{{ $value }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card dashboard-panel h-100 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0 p-4 pb-0">
                        <div class="members-show-section-heading">
                            <h5 class="mb-1">{{ db_trans('member_contributions') }}</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 members-show-finance-table">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('contribution_type') }}</th>
                                        <th>{{ $currentYear }}</th>
                                        <th>{{ $previousYear }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ db_trans('total_zaka') }}</td>
                                        <td>{{ number_format($finance['zaka_current'], 2) }}</td>
                                        <td>{{ number_format($finance['zaka_previous'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ db_trans('total_mavuno') }}</td>
                                        <td>{{ number_format($finance['mavuno_current'], 2) }}</td>
                                        <td>{{ number_format($finance['mavuno_previous'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ db_trans('other_contributions') }}</td>
                                        <td>{{ number_format($finance['other_current'], 2) }}</td>
                                        <td>{{ number_format($finance['other_previous'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ db_trans('offerings') }}</td>
                                        <td>{{ number_format($finance['offering_current'] ?? 0, 2) }}</td>
                                        <td>{{ number_format($finance['offering_previous'] ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="fw-bold total-row">
                                        <td>{{ db_trans('grand_total') }}</td>
                                        <td>{{ number_format($finance['total_current'], 2) }}</td>
                                        <td>{{ number_format($finance['total_previous'], 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="members-show-cta-row mt-4">
                            <a href="#" class="btn btn-outline-info btn-sm rounded-pill px-3">
                                <i class="fas fa-graduation-cap me-2"></i>{{ db_trans('enroll_in_teaching') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection