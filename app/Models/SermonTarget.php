<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SermonTarget extends Model
{
    use HasFactory;
    protected $fillable = ['sermon_id','target_type','target_id','label','meta'];
    protected $casts = ['meta' => 'array'];
    public function sermon(): BelongsTo { return $this->belongsTo(Sermon::class); }
}
