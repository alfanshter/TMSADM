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
<<<<<<<< HEAD:database/migrations/2025_09_09_080313_add_spec_to_item_machines_table.php
        Schema::table('item_machines', function (Blueprint $table) {
            $table->string('spec')->nullable()->after('code');
========
        Schema::table('stock_spareparts', function (Blueprint $table) {
            $table->softDeletes();
>>>>>>>> temp-main:database/migrations/2026_05_25_000000_add_soft_deletes_to_stock_spareparts_table.php
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<<< HEAD:database/migrations/2025_09_09_080313_add_spec_to_item_machines_table.php
        Schema::table('item_machines', function (Blueprint $table) {
            $table->dropColumn('spec');
========
        Schema::table('stock_spareparts', function (Blueprint $table) {
            $table->dropSoftDeletes();
>>>>>>>> temp-main:database/migrations/2026_05_25_000000_add_soft_deletes_to_stock_spareparts_table.php
        });
    }
};
