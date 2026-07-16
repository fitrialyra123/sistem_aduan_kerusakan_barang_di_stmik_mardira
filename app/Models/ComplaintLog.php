<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintLog extends Model
{
    protected $fillable = [
        'complaint_id',
        'actor_id',
        'old_status',
        'new_status',
        'log_message',
        
    ];

    public function complaint() {
        return $this->belongsTo(Complaint::class);
    }

    public function actor() {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
