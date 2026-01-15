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
        Schema::create('sm_subcategories', function (Blueprint $table) {
            $table->string('subcat_id', 50)->primary();
            $table->string('product_id', 50)->nullable()->index();
            $table->string('subcat_name', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sm_subcategories');
    }
};
