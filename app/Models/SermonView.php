<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SermonView extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['sermon_id','sermon_access_token_id','sermon_recipient_id','member_id','ip_address','user_agent','viewed_at'];
    protected $casts = ['viewed_at'=>'datetime'];
    public function sermon(): BelongsTo { return $this->belongsTo(Sermon::class); }
}
