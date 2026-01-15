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
        Schema::create('sm_product_details', function (Blueprint $table) {
            $table->id('detail_id');
            $table->string('product_id', 50)->nullable()->index();
            $table->string('series_id', 50)->nullable()->index();
            $table->string('subcat_id', 50)->nullable()->index();
            $table->string('material_id', 50)->nullable()->index();
            $table->string('warranty_id', 50)->nullable()->index();
            $table->string('certification_id', 50)->nullable()->index();
            $table->string('net_quantity', 50)->nullable();
            $table->string('weight', 50)->nullable();
            $table->string('mrp', 50)->nullable();
            $table->text('contents')->nullable();
            $table->string('item_dimensions', 255)->nullable();
            $table->string('package_dimensions', 255)->nullable();
            $table->string('manufacturer', 255)->nullable();
            $table->string('marketer', 255)->nullable();
            $table->text('customer_care')->nullable();
            $table->longText('description')->nullable();
            $table->string('image', 2000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_product_details');
    }
};
