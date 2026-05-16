<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Sermon extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'rasimu';
    public const STATUS_PUBLISHED = 'imechapishwa';
    public const STATUS_INACTIVE = 'imesitishwa';
    public const STATUS_ARCHIVED = 'imehifadhiwa';

    protected $fillable = [
        'title','slug','summary','body_html','cover_image_path','video_source','video_path','video_url','thumbnail_path',
        'status','is_active','published_at','created_by','updated_by','request_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Sermon $sermon): void {
            if (blank($sermon->slug)) {
                $base = Str::slug($sermon->title ?: Str::random(8));
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->whereKeyNot($sermon->id)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $sermon->slug = $slug;
            }
        });
    }

    public function targets(): HasMany { return $this->hasMany(SermonTarget::class); }
    public function recipients(): HasMany { return $this->hasMany(SermonRecipient::class); }
    public function tokens(): HasMany { return $this->hasMany(SermonAccessToken::class); }
    public function views(): HasMany { return $this->hasMany(SermonView::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function request(): BelongsTo { return $this->belongsTo(SermonRequest::class, 'request_id'); }

    public function isViewable(): bool
    {
        return $this->is_active && $this->status === self::STATUS_PUBLISHED;
    }

    public static function statuses(): array
    {
        return [self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_INACTIVE, self::STATUS_ARCHIVED];
    }
}
