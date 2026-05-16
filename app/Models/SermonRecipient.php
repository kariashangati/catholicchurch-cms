<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SermonRecipient extends Model
{
    use HasFactory;
    public const SMS_NOT_SENT = 'haijatumwa';
    public const SMS_SENT = 'imetumwa';
    public const SMS_FAILED = 'imeshindwa';

    protected $fillable = ['sermon_id','member_id','name','phone','email','familia_id','jumuiya_id','kanda_id','sms_status','sms_sent_at','sms_error'];
    protected $casts = ['sms_sent_at' => 'datetime'];
    public function sermon(): BelongsTo { return $this->belongsTo(Sermon::class); }
    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function token(): HasOne { return $this->hasOne(SermonAccessToken::class); }
}
