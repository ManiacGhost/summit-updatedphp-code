<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductDetail extends Model
{
    use HasFactory;

    protected $table = 'sm_product_details';
    protected $primaryKey = 'detail_id';
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'series_id',
        'subcat_id',
        'material_id',
        'warranty_id',
        'certification_id',
        'net_quantity',
        'weight',
        'mrp',
        'contents',
        'item_dimensions',
        'package_dimensions',
        'manufacturer',
        'marketer',
        'customer_care',
        'description',
        'image'
    ];

    /**
     * Get the product associated with the detail.
     */
    public function product()
    {
        return $this->belongsTo(ProductMain::class, 'product_id', 'product_id');
    }

    /**
     * Get the series associated with the detail.
     */
    public function series()
    {
        return $this->belongsTo(Series::class, 'series_id', 'series_id');
    }

    /**
     * Get the subcategory associated with the detail.
     */
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcat_id', 'subcat_id');
    }

    /**
     * Get the material associated with the detail.
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id', 'material_id');
    }

    /**
     * Get the warranty associated with the detail.
     */
    public function warranty()
    {
        return $this->belongsTo(Warranty::class, 'warranty_id', 'warranty_id');
    }

    /**
     * Get the certification associated with the detail.
     */
    public function certification()
    {
        return $this->belongsTo(Certification::class, 'certification_id', 'cert_id');
    }
}
