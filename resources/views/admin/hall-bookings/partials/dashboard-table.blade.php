<div class="card hb-panel hb-table-panel border-0 h-100">
    <div class="card-body">
        <div class="hb-panel-head"><h5>{{ $title }}</h5></div>
        <div class="table-responsive">
            <table class="table table-sm hb-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ db_trans('reference') }}</th>
                        <th>{{ db_trans('hall') }}</th>
                        <th>{{ db_trans('date') }}</th>
                        <th>{{ db_trans('status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $booking)
                        <tr>
                            <td><a href="{{ route('hall-bookings.bookings.show', $booking) }}" class="fw-bold">{{ $booking->booking_reference }}</a></td>
                            <td>{{ $booking->hall?->name }}</td>
                            <td>{{ optional($booking->booking_date)->format('d M Y') }}</td>
                            <td><span class="hb-status hb-status-{{ $booking->booking_status }}">{{ db_trans($booking->booking_status_label_key) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
