<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRequest extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'platform_id',
        'type_id',
        'region_id',
        'language_id',
        'works_on_id',
        'title',
        'description',
        'source_url',
        'status',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');    

    }

    public function user()      { return $this->belongsTo(User::class); }
    public function category()  { return $this->belongsTo(ProductCategory::class); }
    public function platform()  { return $this->belongsTo(ProductPlatform::class); }
    public function type()      { return $this->belongsTo(ProductType::class); }
    public function region()    { return $this->belongsTo(ProductRegion::class); }
    public function language()  { return $this->belongsTo(ProductLanguage::class); }
    public function worksOn()   { return $this->belongsTo(ProductWorksOn::class); }

}
