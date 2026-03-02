<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\Auth\VerifyEmailNotification;
use App\Notifications\Auth\ResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'social_provider',
        'social_id',
        'avatar',
        'is_active',
        'is_verified',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'is_verified'       => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function isSuperAdmin(): bool
    {
        return $this->roles()->where('name', 'superadmin')->exists();
    }


    // A user may have one seller profile
    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function affiliate()
    {
        return $this->hasOne(Affiliate::class);
    }

    public function referredBy()
    {
        return $this->hasOne(AffiliateReferral::class, 'referred_user_id');
    }

    // If user is buyer, they may have many orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function isInternal(): bool
    {
        return $this->roles()->where('type', 'internal')->exists();
    }

    public function isExternal(): bool
    {
        return $this->roles()->where('type', 'external')->exists();
    }

    public function isSeller(): bool
    {
        return $this->roles()->where('name', 'seller')->exists();
    }

    public function isCustomer(): bool
    {
        return $this->roles()->where('name', 'customer')->exists();
    }

    public function scopeCustomers($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'customer');
        });
    }

    public function scopeSellers($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'seller');
        });
    }

    public function scopeInternalUsers($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('type', 'internal');
        });
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

   public function hasPermission(string $permission): bool
    {
        // Super Admin bypass
        if ($this->roles()->where('name', 'superadmin')->exists()) {
            return true;
        }

        // Direct permission
        if ($this->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        // Via roles
        return $this->roles()->whereHas('permissions', function ($q) use ($permission) {
            $q->where('name', $permission);
        })->exists();
    }


    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function canDelete(): bool
    {
        return $this->isSuperAdmin() || $this->hasRole('admin');
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
