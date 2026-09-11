<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
    protected $fillable = [
        'user_id',
        'phone_number',
        'code',
        'expires_at',
        'verified_at',
        'invalidated_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'invalidated_at' => 'datetime',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}