<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailResponseMessage extends Model
{
    use HasFactory;

    protected $table = 'email_response_messages';

    protected $fillable = [
        'segnalazione_id',
        'type',
        'subject',
        'body',
        'external_message_id',
        'in_reply_to',
        'metadata',
        'sent_at',
        'received_at',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function segnalazione()
    {
        return $this->belongsTo(Segnalazione::class);
    }
}
