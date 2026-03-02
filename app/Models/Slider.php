<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'title', 'subtitle', 'badge_text', 'badge_color',
        'image', 'overlay_color', 'text_color', 'text_position',
        'product_id', 'button_text', 'button_url',
        'position', 'is_active', 'starts_at', 'ends_at',
        'clicks', 'views',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
        'clicks'     => 'integer',
        'views'      => 'integer',
        'position'   => 'integer',
    ];

    /* ── Relationships ──────────────────────────────── */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /* ── Scopes ─────────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeScheduled($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', now())->whereNotNull('ends_at');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderByDesc('created_at');
    }

    /* ── Dynamic Accessors ──────────────────────────── */

    public function getDisplayTitleAttribute()
    {
        return $this->title ?? $this->product?->title;
    }

    public function getDisplaySubtitleAttribute()
    {
        return $this->subtitle ?? $this->product?->short_description;
    }

    public function getDisplayUrlAttribute()
    {
        if ($this->button_url) {
            return $this->button_url;
        }
        return $this->product ? route('products.show', $this->product->slug) : null;
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;
        if (str_starts_with($this->image, 'http')) return $this->image;
        return asset($this->image);
    }

    public function getIsLiveAttribute(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->ends_at && $this->ends_at->isPast()) return false;
        return true;
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) return 'inactive';
        if ($this->starts_at && $this->starts_at->isFuture()) return 'scheduled';
        if ($this->ends_at && $this->ends_at->isPast()) return 'expired';
        return 'live';
    }

    public function getCtrAttribute(): float
    {
        if ($this->views === 0) return 0;
        return round(($this->clicks / $this->views) * 100, 2);
    }

    /* ── Helpers ─────────────────────────────────────── */

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function incrementClicks(): void
    {
        $this->increment('clicks');
    }
}
