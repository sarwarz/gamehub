<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderAddress extends Model
{
    protected $fillable = [
        'order_id','type','name','email','phone',
        'address','city','state','country','postal_code'
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }
}
