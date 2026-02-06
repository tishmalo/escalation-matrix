<?php

namespace Tishmalo\EscalationMatrix\Models;

use Illuminate\Database\Eloquent\Model;

class PackageTicket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'subject',
        'description',
        'priority',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
