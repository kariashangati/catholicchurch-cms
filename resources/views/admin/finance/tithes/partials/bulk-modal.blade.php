<div class="modal fade" id="bulkTitheModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title">{{ db_trans('bulk_tithe_entry') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('finance.tithes.bulk.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>{{ db_trans('member') }}</th>
                                    <th>{{ db_trans('amount') }}</th>
                                    <th>{{ db_trans('date') }}</th>
                                    <th>{{ db_trans('payment_method') }}</th>
                                    <th>{{ db_trans('status') }}</th>
                                    <th>{{ db_trans('notes') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 8; $i++)
                                    <tr>
                                        <td>
                                            <select name="rows[{{ $i }}][member_id]" class="form-select">
                                                <option value=""></option>
                                                @foreach($members as $member)
                                                    <option value="{{ $member->id }}">{{ $member->full_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" min="1" step="0.01" name="rows[{{ $i }}][amount]" class="form-control"></td>
                                        <td><input type="date" name="rows[{{ $i }}][contribution_date]" class="form-control" value="{{ now()->toDateString() }}"></td>
                                        <td>
                                            <select name="rows[{{ $i }}][payment_method]" class="form-select">
                                                <option value=""></option>
                                                @foreach($paymentMethods as $method)
                                                    <option value="{{ $method }}">{{ ucfirst($method) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="rows[{{ $i }}][status]" class="form-select">
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="rows[{{ $i }}][notes]" class="form-control"></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ db_trans('save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
