<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'avatar', 'dob', 'gender',
        'phone', 'alternate_phone',
        'company', 'tax_id', 'newsletter_subscribed',
        'preferred_currency', 'preferred_language',
    ];

    protected $casts = [
        'dob'                   => 'date',
        'newsletter_subscribed' => 'boolean',
        'last_login_at'         => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor for full name
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
