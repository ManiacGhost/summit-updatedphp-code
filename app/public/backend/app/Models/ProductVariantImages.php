<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantImages extends Model
{
    use HasFactory;

    protected $table = 'product_variant_images'; // ✅ explicit table name (sometimes needed)
    
    protected $fillable = ['product_variant_id', 'image'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
