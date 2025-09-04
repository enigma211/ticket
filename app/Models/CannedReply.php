<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CannedReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'title',
        'body',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(CannedGroup::class, 'group_id');
    }
}


