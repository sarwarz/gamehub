<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'disk',
        'directory',
        'filename',
        'original_name',
        'mime_type',
        'extension',
        'type',
        'size',
        'meta',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_primary' => 'boolean',
    ];

    /* ============================
     | Relationships
     ============================ */
    public function mediable()
    {
        return $this->morphTo();
    }

    /* ============================
     | Accessors
     ============================ */
    public function getPathAttribute(): string
    {
        return trim($this->directory . '/' . $this->filename, '/');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    

    /* ============================
     | Scopes
     ============================ */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    

}
