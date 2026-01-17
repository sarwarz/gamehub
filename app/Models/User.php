<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

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
        'role',
        'is_active',
        'is_seller',
        'is_verified',
        'avatar',
        'phone',
        'country',
        'city',
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
            'is_seller'         => 'boolean',
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
        return $this->is_super_admin === true;
    }


    // A user may have one seller profile
    public function seller()
    {
        return $this->hasOne(Seller::class);
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


   public function hasPermission(string $permission): bool
    {
        // Super Admin bypass
        if ($this->is_super_admin) {
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







}
