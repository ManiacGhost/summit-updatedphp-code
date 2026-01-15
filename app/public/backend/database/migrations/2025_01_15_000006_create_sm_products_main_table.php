<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sm_products_main', function (Blueprint $table) {
            $table->string('product_id', 50)->primary();
            $table->string('category_id', 20)->nullable()->index();
            $table->string('product_name', 255)->nullable();
            $table->string('hsn_code', 20)->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable();

            $table->foreign('category_id')
                ->references('category_id')
                ->on('sm_product_categories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_products_main');
    }
};
