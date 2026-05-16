<div class="modal fade" id="titheModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title" id="titheModalTitle">{{ db_trans('record_tithe') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="titheForm">
                @csrf
                <div id="titheFormMethod"></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('member') }}</label>
                            <select name="member_id" id="tithe_member_id" class="form-select" required>
                                <option value="">{{ db_trans('all_members') }}</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}">{{ $member->full_name }}{{ $member->member_code ? ' - '.$member->member_code : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ db_trans('amount') }}</label>
                            <input type="number" min="1" step="0.01" name="amount" id="tithe_amount" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ db_trans('date') }}</label>
                            <input type="date" name="contribution_date" id="tithe_contribution_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('payment_method') }}</label>
                            <select name="payment_method" id="tithe_payment_method" class="form-select">
                                <option value="">{{ db_trans('all_methods') }}</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method }}">{{ ucfirst($method) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('reference_no') }}</label>
                            <input type="text" name="reference_no" id="tithe_reference_no" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('receipt_no') }}</label>
                            <input type="text" name="receipt_no" id="tithe_receipt_no" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="status" id="tithe_status" class="form-select" required>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ db_trans('notes') }}</label>
                            <textarea name="notes" id="tithe_notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" value="1" id="tithe_send_sms" name="send_sms">
                                <label class="form-check-label" for="tithe_send_sms">{{ db_trans('send_sms_acknowledgement') }}</label>
                            </div>
                        </div>
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
