<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedMail extends Model
{
    protected $table = 'failed_mails';

    protected $fillable = [
        'recipient',
        'subject',
        'mailable_class',
        'payload',
        'error_message',
        'status',
        'attempts',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
    ];
}