<?php

namespace App\Models;

use App\Support\Receipts\ReceiptChannels;
use App\Support\Receipts\ReceiptLayouts;
use App\Support\Receipts\ReceiptStatuses;
use App\Support\Receipts\ReceiptTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceiptIssue extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'receipt_issues';

    protected $fillable = [
        'receipt_no',
        'verification_code',
        'access_token',
        'access_expires_at',
        'access_token_expires_at',
        'receipt_type',
        'receipt_layout',
        'status',
        'channel',
        'source_type',
        'source_id',
        'member_id',
        'familia_id',
        'jumuiya_id',
        'kanda_id',
        'contribution_type_id',
        'period_month',
        'period_year',
        'amount',
        'currency',
        'delivery_status',
        'sms_delivery_status',
        'sms_sent_at',
        'sms_sent_to',
        'sms_sent_by',
        'sms_template_key',
        'last_delivery_channel',
        'delivery_attempts',
        'issued_by',
        'approved_by',
        'issued_at',
        'printed_at',
        'last_printed_at',
        'printed_by',
        'last_printed_by',
        'print_count',
        'downloaded_at',
        'first_opened_at',
        'last_opened_at',
        'first_downloaded_at',
        'last_downloaded_at',
        'download_count',
        'verified_at',
        'verified_by',
        'verification_count',
        'pdf_path',
        'batch_reference',
        'meta',
        'link_enabled',
        'link_revoked_at',
        'reissued_from_id',
        'voided_at',
        'voided_by',
        'void_reason',
        'is_void',
        'void_reference_receipt_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'contribution_date' => 'date',
            'period_month' => 'integer',
            'period_year' => 'integer',
            'issued_at' => 'datetime',
            'approved_at' => 'datetime',
            'printed_at' => 'datetime',
            'last_printed_at' => 'datetime',
            'sms_sent_at' => 'datetime',
            'first_opened_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'first_downloaded_at' => 'datetime',
            'last_downloaded_at' => 'datetime',
            'downloaded_at' => 'datetime',
            'access_expires_at' => 'datetime',
            'access_token_expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'voided_at' => 'datetime',
            'download_count' => 'integer',
            'print_count' => 'integer',
            'verification_count' => 'integer',
            'link_enabled' => 'boolean',
            'is_void' => 'boolean',
            'meta' => 'array',
        ];
    }

    public static function availableStatuses(): array
    {
        return ReceiptStatuses::values();
    }

    public static function availableTypes(): array
    {
        return ReceiptTypes::values();
    }

    public static function availableLayouts(): array
    {
        return ReceiptLayouts::values();
    }

    public static function availableChannels(): array
    {
        return ReceiptChannels::values();
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function familia(): BelongsTo
    {
        return $this->belongsTo(Familia::class);
    }

    public function jumuiya(): BelongsTo
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function kanda(): BelongsTo
    {
        return $this->belongsTo(Kanda::class);
    }

    public function contributionType(): BelongsTo
    {
        return $this->belongsTo(ContributionType::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function issuer(): BelongsTo
    {
        return $this->issuedBy();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function printedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function actionLogs(): HasMany
    {
        return $this->hasMany(ReceiptActionLog::class);
    }

    public function deliveryLogs(): HasMany
    {
        return $this->hasMany(ReceiptDeliveryLog::class);
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(ReceiptAccessLog::class);
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(ReceiptVerificationLog::class);
    }

    public function exceptionLogs(): HasMany
    {
        return $this->hasMany(ReceiptExceptionLog::class);
    }

    public function getRecipientNameAttribute(): string
    {
        return (string) data_get(
            $this->meta,
            'recipient_name',
            $this->member?->full_name
                ?? $this->member?->name
                ?? $this->jumuiya?->name
                ?? '-'
        );
    }

    public function getDisplayStatusAttribute(): string
    {
        return db_trans('receipts.status.' . ($this->status ?: ReceiptStatuses::PENDING_ISSUE));
    }

    public function getVerificationUrlAttribute(): string
    {
        return route('receipt.verify.search', [
            'receipt_no' => $this->receipt_no,
            'verification_code' => $this->verification_code,
        ]);
    }

    public function getAccessUrlAttribute(): string
    {
        return route('receipt.access', ['token' => $this->access_token]);
    }
}
