@extends('pdf.layouts.report')

@section('content')
    @php
        $reportType = $reportType ?? 'dashboard';
    @endphp

    @if($reportType === 'dashboard')
        <div class="section-header">
            <h3>{{ db_trans('leadership_positions') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 70%;">{{ db_trans('position') }}</th>
                        <th class="text-right" style="width: 30%;">{{ db_trans('active_leaders') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leadersByPosition as $row)
                        <tr>
                            <td>{{ $row->position?->name ?? '—' }}</td>
                            <td class="text-right tabular-nums">{{ number_format((int) ($row->total ?? 0)) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center muted">{{ db_trans('no_records_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section-header">
            <h3>{{ db_trans('recent_leadership_activity') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 28%;">{{ db_trans('member') }}</th>
                        <th style="width: 28%;">{{ db_trans('position') }}</th>
                        <th style="width: 26%;">{{ db_trans('scope') }}</th>
                        <th style="width: 18%;">{{ db_trans('status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAssignments as $assignment)
                        <tr>
                            <td>{{ $assignment->member?->full_name ?? '—' }}</td>
                            <td>{{ $assignment->position?->name ?? '—' }}</td>
                            <td>{{ $assignment->jumuiya?->name ?? $assignment->kanda?->name ?? $assignment->apostolicGroup?->name ?? db_trans($assignment->scope_type) }}</td>
                            <td>{{ $assignment->status_label ?? $assignment->status ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center muted">{{ db_trans('no_records_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($reportType === 'assignments')
        <div class="section-header">
            <h3>{{ db_trans('leadership_assignments') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 5%;">#</th>
                        <th style="width: 18%;">{{ db_trans('member') }}</th>
                        <th style="width: 18%;">{{ db_trans('position') }}</th>
                        <th style="width: 16%;">{{ db_trans('scope') }}</th>
                        <th class="text-center" style="width: 12%;">{{ db_trans('started_at') }}</th>
                        <th class="text-center" style="width: 12%;">{{ db_trans('ended_at') }}</th>
                        <th style="width: 11%;">{{ db_trans('user') }}</th>
                        <th style="width: 8%;">{{ db_trans('status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $assignment->member?->full_name ?? '—' }}</td>
                            <td>{{ $assignment->position?->name ?? '—' }}</td>
                            <td>{{ $assignment->jumuiya?->name ?? $assignment->kanda?->name ?? $assignment->apostolicGroup?->name ?? db_trans($assignment->scope_type) }}</td>
                            <td class="text-center">{{ optional($assignment->started_at)->format('d M Y') ?: '—' }}</td>
                            <td class="text-center">{{ optional($assignment->ended_at)->format('d M Y') ?: '—' }}</td>
                            <td>{{ $assignment->user?->name ?? '—' }}</td>
                            <td>{{ $assignment->status_label ?? $assignment->status ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center muted">{{ db_trans('no_records_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($reportType === 'positions')
        <div class="section-header">
            <h3>{{ db_trans('leadership_positions') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 6%;">#</th>
                        <th style="width: 25%;">{{ db_trans('name') }}</th>
                        <th style="width: 17%;">{{ db_trans('scope') }}</th>
                        <th style="width: 18%;">{{ db_trans('committee_type') }}</th>
                        <th style="width: 20%;">{{ db_trans('auto_role') }}</th>
                        <th class="text-center" style="width: 7%;">{{ db_trans('display_order') }}</th>
                        <th style="width: 7%;">{{ db_trans('status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($positions as $position)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $position->name }}</td>
                            <td>{{ db_trans($position->level_type) }}</td>
                            <td>{{ db_trans($position->committee_type) }}</td>
                            <td>{{ $position->auto_role_name ?: '—' }}</td>
                            <td class="text-center tabular-nums">{{ number_format((int) ($position->display_order ?? 0)) }}</td>
                            <td>{{ $position->is_active ? db_trans('active') : db_trans('inactive') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center muted">{{ db_trans('no_records_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection
