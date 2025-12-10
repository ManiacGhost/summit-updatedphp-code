<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'product_variant_id', 'quantity', 'price'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')
                    ->with('product', 'attributes.attribute');
        // return $this->belongsTo(ProductVariant::class, 'product_variant_id')
        //             ->with('product', 'images', 'attributes.attribute');
    }
}
