<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SermonRequest extends Model
{
    use HasFactory;
    public const STATUS_RECEIVED = 'limepokelewa';
    public const STATUS_PREPARING = 'linaandaliwa';
    public const STATUS_SENT = 'limetumwa';
    public const STATUS_CLOSED = 'limefungwa';
    public const STATUS_REJECTED = 'limekataliwa';

    protected $fillable = ['request_reference','member_id','name','phone','email','jumuiya_id','topic','message','preferred_response_type','status','assigned_to','linked_sermon_id','admin_note'];
    protected static function booted(): void
    {
        static::creating(function (SermonRequest $request): void {
            if (blank($request->request_reference)) {
                do { $ref = 'MH-'.now()->format('ymd').'-'.Str::upper(Str::random(5)); }
                while (static::where('request_reference', $ref)->exists());
                $request->request_reference = $ref;
            }
        });
    }
    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function jumuiya(): BelongsTo { return $this->belongsTo(Jumuiya::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function linkedSermon(): BelongsTo { return $this->belongsTo(Sermon::class, 'linked_sermon_id'); }
    public function logs(): HasMany { return $this->hasMany(SermonRequestLog::class); }
    public static function statuses(): array { return [self::STATUS_RECEIVED,self::STATUS_PREPARING,self::STATUS_SENT,self::STATUS_CLOSED,self::STATUS_REJECTED]; }
}
