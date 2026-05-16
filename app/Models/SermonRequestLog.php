<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SermonRequestLog extends Model
{
    use HasFactory;
    protected $fillable = ['sermon_request_id','old_status','new_status','note','changed_by'];
    public function request(): BelongsTo { return $this->belongsTo(SermonRequest::class, 'sermon_request_id'); }
    public function changer(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
