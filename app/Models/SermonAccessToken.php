<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SermonAccessToken extends Model
{
    use HasFactory;
    protected $fillable = ['sermon_id','sermon_recipient_id','member_id','token','expires_at','opened_at','last_opened_at','open_count','is_active'];
    protected $casts = ['expires_at'=>'datetime','opened_at'=>'datetime','last_opened_at'=>'datetime','open_count'=>'integer','is_active'=>'boolean'];
    public function sermon(): BelongsTo { return $this->belongsTo(Sermon::class); }
    public function recipient(): BelongsTo { return $this->belongsTo(SermonRecipient::class, 'sermon_recipient_id'); }
    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function isUsable(): bool { return $this->is_active && (! $this->expires_at || $this->expires_at->isFuture()) && $this->sermon?->isViewable(); }
}
