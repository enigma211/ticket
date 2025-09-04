<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalMessage extends Model
{
    protected $table = 'internal_messages';

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'thread_id',
        'subject',
        'body',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(InternalMessage::class, 'thread_id');
    }

    public function getRootSubjectAttribute(): ?string
    {
        return $this->thread->subject ?? $this->subject;
    }
}


