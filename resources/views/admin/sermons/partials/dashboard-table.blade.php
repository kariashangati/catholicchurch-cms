<div class="col-xl-4">
    <div class="card sermon-panel border-0 h-100">
        <div class="card-body">
            <div class="sermon-panel-head"><h5>{{ $title }}</h5></div>
            <div class="table-responsive">
                <table class="table table-sm sermon-table mb-0">
                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            @if($type === 'sermons')
                                <td><strong>{{ $row->title }}</strong><div class="small text-muted">{{ db_trans('recipients') }}: {{ $row->recipients_count }} · {{ db_trans('views') }}: {{ $row->views_count }}</div></td><td><span class="sermon-status sermon-status-{{ $row->status }}">{{ db_trans('sermon_status_'.$row->status) }}</span></td>
                            @elseif($type === 'requests')
                                <td><strong>{{ $row->topic }}</strong><div class="small text-muted">{{ $row->name }} · {{ $row->phone }}</div></td><td><span class="sermon-status sermon-status-{{ $row->status }}">{{ db_trans('sermon_request_status_'.$row->status) }}</span></td>
                            @else
                                <td><strong>{{ $row->name }}</strong><div class="small text-muted">{{ $row->sermon?->title }}</div></td><td><span class="sermon-status sermon-sms-{{ $row->sms_status }}">{{ db_trans('sms_status_'.$row->sms_status) }}</span></td>
                            @endif
                        </tr>
                    @empty
                        <tr><td class="text-muted py-3">{{ db_trans('no_records_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
