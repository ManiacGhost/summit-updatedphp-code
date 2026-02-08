<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the view to include trending_flag from sm_products_main
        DB::statement("
            CREATE OR REPLACE VIEW vw_product_full_view AS
            SELECT 
                pd.*,
                pm.category_id,
                pm.product_name,
                pm.hsn_code,
                pm.tax_rate,
                pm.trending_flag,
                pc.category_name as master_category,
                pc.sort_order as category_sort_order,
                sc.subcat_name,
                s.series_name,
                m.material_name,
                w.warranty_text,
                c.certification
            FROM sm_product_details pd
            LEFT JOIN sm_products_main pm ON pd.product_id = pm.product_id
            LEFT JOIN sm_product_categories pc ON pm.category_id = pc.category_id
            LEFT JOIN sm_subcategories sc ON pd.subcat_id = sc.subcat_id
            LEFT JOIN sm_series s ON pd.series_id = s.series_id
            LEFT JOIN sm_materials m ON pd.material_id = m.material_id
            LEFT JOIN sm_warranty w ON pd.warranty_id = w.warranty_id
            LEFT JOIN sm_certification c ON pd.cert_id = c.cert_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate view without trending_flag
        DB::statement("
            CREATE OR REPLACE VIEW vw_product_full_view AS
            SELECT 
                pd.*,
                pm.category_id,
                pm.product_name,
                pm.hsn_code,
                pm.tax_rate,
                pc.category_name as master_category,
                pc.sort_order as category_sort_order,
                sc.subcat_name,
                s.series_name,
                m.material_name,
                w.warranty_text,
                c.certification
            FROM sm_product_details pd
            LEFT JOIN sm_products_main pm ON pd.product_id = pm.product_id
            LEFT JOIN sm_product_categories pc ON pm.category_id = pc.category_id
            LEFT JOIN sm_subcategories sc ON pd.subcat_id = sc.subcat_id
            LEFT JOIN sm_series s ON pd.series_id = s.series_id
            LEFT JOIN sm_materials m ON pd.material_id = m.material_id
            LEFT JOIN sm_warranty w ON pd.warranty_id = w.warranty_id
            LEFT JOIN sm_certification c ON pd.cert_id = c.cert_id
        ");
    }
};
