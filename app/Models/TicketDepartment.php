<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TicketDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'color', 'icon',
        'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $dept) {
            if (empty($dept->slug)) {
                $dept->slug = Str::slug($dept->name);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function tickets()
    {
        return $this->hasMany(SupportTicket::class, 'department', 'slug');
    }
}
