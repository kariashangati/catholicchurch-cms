@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('summary') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-right">{{ db_trans('members') }}</th>
                    <th class="text-right">{{ db_trans('baptized') }}</th>
                    <th class="text-right">{{ db_trans('communion') }}</th>
                    <th class="text-right">{{ db_trans('confirmation') }}</th>
                    <th class="text-right">{{ db_trans('married') }}</th>
                    <th class="text-right">{{ db_trans('receiving_eucharist') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-right tabular-nums">{{ number_format((int) ($summary['members_total'] ?? 0)) }}</td>
                    <td class="text-right tabular-nums">{{ number_format((int) ($summary['baptized'] ?? 0)) }}</td>
                    <td class="text-right tabular-nums">{{ number_format((int) ($summary['communion'] ?? 0)) }}</td>
                    <td class="text-right tabular-nums">{{ number_format((int) ($summary['confirmation'] ?? 0)) }}</td>
                    <td class="text-right tabular-nums">{{ number_format((int) ($summary['married'] ?? 0)) }}</td>
                    <td class="text-right tabular-nums">{{ number_format((int) ($summary['receives_eucharist'] ?? 0)) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($isFamilyReport)
        <div class="section-header">
            <h3>{{ db_trans('parents_and_heads') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-center nowrap">#</th>
                        <th>{{ db_trans('name') }}</th>
                        <th>{{ db_trans('phone') }}</th>
                        <th>{{ db_trans('family_role') }}</th>
                        <th>{{ db_trans('gender') }}</th>
                        <th>{{ db_trans('married') }}</th>
                        <th>{{ db_trans('marriage_type') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parents as $parent)
                        <tr>
                            <td class="text-center tabular-nums">{{ $loop->iteration }}</td>
                            <td>{{ $parent->full_name }}</td>
                            <td>{{ $parent->phone ?: '—' }}</td>
                            <td>{{ $parent->family_role ?: '—' }}</td>
                            <td>{{ ucfirst((string) $parent->gender) }}</td>
                            <td>{{ $parent->is_married ? db_trans('yes') : db_trans('no') }}</td>
                            <td>{{ $parent->marriage_type ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ db_trans('no_data_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section-header">
            <h3>{{ db_trans('family_members') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-center nowrap">#</th>
                        <th>{{ db_trans('name') }}</th>
                        <th>{{ db_trans('gender') }}</th>
                        <th>{{ db_trans('baptized') }}</th>
                        <th>{{ db_trans('communion') }}</th>
                        <th>{{ db_trans('confirmation') }}</th>
                        <th>{{ db_trans('receiving_eucharist') }}</th>
                        <th>{{ db_trans('married') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td class="text-center tabular-nums">{{ $loop->iteration }}</td>
                            <td>{{ $member->full_name }}</td>
                            <td>{{ ucfirst((string) $member->gender) }}</td>
                            <td>{{ $member->is_baptized ? db_trans('yes') : db_trans('no') }}</td>
                            <td>{{ $member->has_communion ? db_trans('yes') : db_trans('no') }}</td>
                            <td>{{ $member->has_confirmation ? db_trans('yes') : db_trans('no') }}</td>
                            <td>{{ $member->receives_eucharist ? db_trans('yes') : db_trans('no') }}</td>
                            <td>{{ $member->is_married ? db_trans('yes') : db_trans('no') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">{{ db_trans('no_data_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="section-header">
            <h3>{{ db_trans('summary') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ db_trans('scope') }}</th>
                        <th class="text-right">{{ db_trans('members') }}</th>
                        <th class="text-right">{{ db_trans('baptized') }}</th>
                        <th class="text-right">{{ db_trans('communion') }}</th>
                        <th class="text-right">{{ db_trans('confirmation') }}</th>
                        <th class="text-right">{{ db_trans('married') }}</th>
                        <th class="text-right">{{ db_trans('receiving_eucharist') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td class="text-right tabular-nums">{{ $row['members_total'] }}</td>
                            <td class="text-right tabular-nums">{{ $row['baptized'] }}</td>
                            <td class="text-right tabular-nums">{{ $row['communion'] }}</td>
                            <td class="text-right tabular-nums">{{ $row['confirmation'] }}</td>
                            <td class="text-right tabular-nums">{{ $row['married'] }}</td>
                            <td class="text-right tabular-nums">{{ $row['receives_eucharist'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ db_trans('no_data_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section-header">
            <h3>{{ db_trans('members') }}</h3>
            <div class="accent-line"></div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-center nowrap">#</th>
                        <th>{{ db_trans('name') }}</th>
                        <th>{{ db_trans('familia') }}</th>
                        <th>{{ db_trans('jumuiya') }}</th>
                        <th>{{ db_trans('kanda') }}</th>
                        <th>{{ db_trans('gender') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td class="text-center tabular-nums">{{ $loop->iteration }}</td>
                            <td>{{ $member->full_name }}</td>
                            <td>{{ $member->familia?->name ?? '—' }}</td>
                            <td>{{ $member->familia?->jumuiya?->name ?? '—' }}</td>
                            <td>{{ $member->familia?->jumuiya?->kanda?->name ?? '—' }}</td>
                            <td>{{ ucfirst((string) $member->gender) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">{{ db_trans('no_data_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection