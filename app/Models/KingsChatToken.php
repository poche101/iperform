<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KingsChatToken extends Model
{
    protected $table = 'kingschat_tokens';

    protected $fillable = ['access_token', 'refresh_token', 'expires_at'];

    protected $casts = [
        'access_token'  => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at'    => 'datetime',
    ];
}
