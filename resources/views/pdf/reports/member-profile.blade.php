@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ $reportTitle }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container report-meta-box">
        <table class="data-table">
            <tbody>
                <tr>
                    <th style="width: 25%;">{{ db_trans('member') }}</th>
                    <td style="width: 25%;">{{ $member->full_name }}</td>
                    <th style="width: 25%;">{{ db_trans('member_code') }}</th>
                    <td style="width: 25%;">{{ $member->member_code ?: '—' }}</td>
                </tr>
                <tr>
                    <th>{{ db_trans('phone') }}</th>
                    <td>{{ $member->phone ?: '—' }}</td>
                    <th>{{ db_trans('date_of_birth') }}</th>
                    <td>{{ optional($member->date_of_birth)->format('d M Y') ?: '—' }}</td>
                </tr>
                <tr>
                    <th>{{ db_trans('occupation') }}</th>
                    <td>{{ $member->occupation ?: '—' }}</td>
                    <th>{{ db_trans('status') }}</th>
                    <td>{{ $member->is_active ? db_trans('active') : db_trans('inactive') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section-header">
        <h3>{{ db_trans('church_membership') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container report-meta-box">
        <table class="data-table">
            <tbody>
                <tr>
                    <th>{{ db_trans('familia') }}</th>
                    <td>{{ $member->familia?->name ?: '—' }}</td>
                    <th>{{ db_trans('jumuiya') }}</th>
                    <td>{{ $member->familia?->jumuiya?->name ?: '—' }}</td>
                </tr>
                <tr>
                    <th>{{ db_trans('kanda') }}</th>
                    <td>{{ $member->familia?->jumuiya?->kanda?->name ?: '—' }}</td>
                    <th>{{ db_trans('family_role') }}</th>
                    <td>{{ $member->family_role ? db_trans(strtolower($member->family_role)) : '—' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section-header">
        <h3>{{ db_trans('sacrament_journey') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container report-meta-box">
        <table class="data-table">
            <tbody>
                <tr>
                    <th>{{ db_trans('baptized') }}</th>
                    <td>{{ $member->is_baptized ? db_trans('yes') : db_trans('no') }}</td>
                    <th>{{ db_trans('communion') }}</th>
                    <td>{{ $member->has_communion ? db_trans('yes') : db_trans('no') }}</td>
                </tr>
                <tr>
                    <th>{{ db_trans('confirmation') }}</th>
                    <td>{{ $member->has_confirmation ? db_trans('yes') : db_trans('no') }}</td>
                    <th>{{ db_trans('eucharist') }}</th>
                    <td>{{ $member->receives_eucharist ? db_trans('yes') : db_trans('no') }}</td>
                </tr>
                <tr>
                    <th>{{ db_trans('married') }}</th>
                    <td>{{ $member->is_married ? db_trans('yes') : db_trans('no') }}</td>
                    <th>{{ db_trans('marriage_type') }}</th>
                    <td>{{ $member->marriage_type ? db_trans(strtolower($member->marriage_type)) : '—' }}</td>
                </tr>
                <tr>
                    <th>{{ db_trans('baptism_certificate_number') }}</th>
                    <td>{{ $member->baptism_certificate_number ?: '—' }}</td>
                    <th>{{ db_trans('marriage_certificate_number') }}</th>
                    <td>{{ $member->marriage_certificate_number ?: '—' }}</td>
                </tr>
                <tr>
                    <th>{{ db_trans('baptism_parish') }}</th>
                    <td>{{ $member->baptism_parish ?: '—' }}</td>
                    <th>{{ db_trans('baptism_diocese') }}</th>
                    <td>{{ $member->baptism_diocese ?: '—' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section-header">
        <h3>{{ db_trans('financial_summary') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ db_trans('contribution_type') }}</th>
                    <th class="text-right">{{ $currentYear }}</th>
                    <th class="text-right">{{ $previousYear }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ db_trans('total_zaka') }}</td>
                    <td class="text-right tabular-nums">{{ number_format($finance['zaka_current'], 2) }}</td>
                    <td class="text-right tabular-nums">{{ number_format($finance['zaka_previous'], 2) }}</td>
                </tr>
                <tr>
                    <td>{{ db_trans('total_mavuno') }}</td>
                    <td class="text-right tabular-nums">{{ number_format($finance['mavuno_current'], 2) }}</td>
                    <td class="text-right tabular-nums">{{ number_format($finance['mavuno_previous'], 2) }}</td>
                </tr>
                <tr>
                    <td>{{ db_trans('other_contributions') }}</td>
                    <td class="text-right tabular-nums">{{ number_format($finance['other_current'], 2) }}</td>
                    <td class="text-right tabular-nums">{{ number_format($finance['other_previous'], 2) }}</td>
                </tr>
                <tr>
                    <td>{{ db_trans('offerings') }}</td>
                    <td class="text-right tabular-nums">{{ number_format($finance['offering_current'], 2) }}</td>
                    <td class="text-right tabular-nums">{{ number_format($finance['offering_previous'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>{{ db_trans('grand_total') }}</strong></td>
                    <td class="text-right tabular-nums"><strong>{{ number_format($finance['total_current'], 2) }}</strong></td>
                    <td class="text-right tabular-nums"><strong>{{ number_format($finance['total_previous'], 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection