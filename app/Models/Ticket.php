<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'assigned_to',
        'department_id',
        'title',
        'description',
        'status',
        'tracking_code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TicketAssignment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open' => 'باز',
            'awaiting_user' => 'پاسخ داده شده',
            'closed' => 'بسته',
            'auto_closed' => 'بسته خودکار',
            default => (string) $this->status,
        };
    }
}
