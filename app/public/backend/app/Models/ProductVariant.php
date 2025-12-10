<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
        'image'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function images()
    {
        $query = $this->hasMany(ProductVariantImages::class);
        return $query;
    }






    public function attributes()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'product_variant_attributes',
            'product_variant_id',
            'attribute_value_id'
        );
    }
}
