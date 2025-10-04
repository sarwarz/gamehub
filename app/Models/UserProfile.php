<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'avatar', 'dob', 'gender',
        'phone', 'alternate_phone',
        'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country',
        'company', 'tax_id', 'newsletter_subscribed', 'is_verified',
        'preferred_currency', 'preferred_language',
        'last_login_at', 'last_login_ip'
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
