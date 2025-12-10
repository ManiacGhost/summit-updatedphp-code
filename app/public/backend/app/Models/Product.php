<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'description',
        'short_description',
        'meta_title',
        'meta_description'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function related()
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id')
            ->where('id', '!=', $this->id)
            ->limit(4);
    }


    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
