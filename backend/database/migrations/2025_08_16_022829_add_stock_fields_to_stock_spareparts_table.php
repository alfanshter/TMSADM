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
        Schema::table('stock_spareparts', function (Blueprint $table) {
            $table->integer('stok_awal')->default(0)->after('stok');
            $table->integer('incoming')->default(0)->after('stok_awal');
            $table->integer('usage')->default(0)->after('incoming');
            $table->integer('end_month_stock')->default(0)->after('usage');
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_spareparts', function (Blueprint $table) {
            $table->dropColumn(['stok_awal', 'incoming', 'usage', 'end_month_stock']);

        });
    }
};
