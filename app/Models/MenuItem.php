<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id', 'parent_id', 'title', 'type', 'columns', 'url', 'icon',
        'badge_text', 'badge_color', 'target', 'position', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    public function activeChildren()
    {
        return $this->children()->where('is_active', true);
    }

    public function allActiveChildren()
    {
        return $this->activeChildren()->with('allActiveChildren');
    }
}
