<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'message_id',
        'mailable',
        'subject',
        'to',
        'status',
        'error',
        'queued_at',
        'sent_at',
        'failed_at',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];
}


