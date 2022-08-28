<?php

namespace App\Models;

use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function productVariant()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function productVariantPrice()
    {
        return $this->hasMany(ProductVariantPrice::class);
    }
    public function scopeApplyFilter($query, $request)
    {
        return $query->when($request->title, function($q) use($request) {
             $q->where('title','like', '%'.$request->title.'%');
        })
        ->when($request->date, function($q) use($request) {
             $q->whereDate('created_at',$request->date);
        });
    }
}
