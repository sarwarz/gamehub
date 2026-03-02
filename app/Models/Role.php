<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Role extends Model
{
    protected $fillable = ['name', 'label', 'type', 'is_system'];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    public function givePermissionTo($permission)
    {
        return $this->permissions()->attach($permission);
    }

    public function hasPermission($permission)
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('type', 'internal');
    }

    public function scopeExternal(Builder $query): Builder
    {
        return $query->where('type', 'external');
    }

    public function isInternal(): bool
    {
        return $this->type === 'internal';
    }

    public function isExternal(): bool
    {
        return $this->type === 'external';
    }
}
