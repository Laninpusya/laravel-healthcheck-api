<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthCheckRequest extends Model
{
    protected $fillable = [
        'owner_uuid',
        'method',
        'path',
        'ip_address',
        'status_code',
        'response_payload',
    ];

    protected $casts = [
        'response_payload' => 'array',
    ];
}
