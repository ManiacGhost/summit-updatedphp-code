<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'sm_product_categories';
    protected $primaryKey = 'category_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'category_name',
        'sort_order'
    ];

    protected $casts = [
        'sort_order' => 'integer'
    ];
}
