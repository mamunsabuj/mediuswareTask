<?php

namespace App\Models;

use App\Models\Variant;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'variant', 'variant_id', 'product_id'
    ];

    public function variantType()
    {
        return $this->belongsTo(Variant::class, 'variant_id','id');
    }

}
