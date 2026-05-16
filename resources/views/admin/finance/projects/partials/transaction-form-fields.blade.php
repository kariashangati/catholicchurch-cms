@php
    $transactionStatuses = $transactionStatuses ?? \App\Models\ProjectTransaction::availableStatuses();
    $transactionTypes = $transactionTypes ?? \App\Models\ProjectTransaction::availableTypes();
    $paymentMethods = $paymentMethods ?? \App\Models\ProjectTransaction::availablePaymentMethods();
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('project') }}</label>
        <select name="project_id" class="form-select" required>
            <option value="">{{ db_trans('select_project') }}</option>
            @foreach($projects as $projectItem)
                <option value="{{ $projectItem->id }}" @selected((int) old('project_id', $transaction?->project_id) === (int) $projectItem->id)>
                    {{ $projectItem->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">{{ db_trans('type') }}</label>
        <select name="transaction_type" class="form-select" required>
            @foreach($transactionTypes as $type)
                <option value="{{ $type }}" @selected(old('transaction_type', $transaction?->transaction_type ?? \App\Models\ProjectTransaction::TYPE_INCOME) === $type)>
                    {{ db_trans($type) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">{{ db_trans('status') }}</label>
        <select name="status" class="form-select" required>
            @foreach($transactionStatuses as $status)
                <option value="{{ $status }}" @selected(old('status', $transaction?->status ?? \App\Models\ProjectTransaction::STATUS_PENDING) === $status)>
                    {{ db_trans($status) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('amount') }}</label>
        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount', $transaction?->amount) }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('transaction_date') }}</label>
        <input type="date" name="transaction_date" class="form-control" value="{{ old('transaction_date', optional($transaction?->transaction_date)->format('Y-m-d')) }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">{{ db_trans('payment_method') }}</label>
        <select name="payment_method" class="form-select">
            <option value="">{{ db_trans('optional') }}</option>
            @foreach($paymentMethods as $method)
                <option value="{{ $method }}" @selected(old('payment_method', $transaction?->payment_method) === $method)>
                    {{ db_trans($method) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ db_trans('reference_no') }}</label>
        <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no', $transaction?->reference_no) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ db_trans('receipt_no') }}</label>
        <input type="text" name="receipt_no" class="form-control" value="{{ old('receipt_no', $transaction?->receipt_no) }}">
    </div>

    <div class="col-12">
        <label class="form-label">{{ db_trans('description') }}</label>
        <textarea name="description" class="form-control" rows="4">{{ old('description', $transaction?->description) }}</textarea>
    </div>
</div>