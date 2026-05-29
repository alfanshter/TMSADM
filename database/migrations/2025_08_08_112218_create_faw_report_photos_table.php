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
<<<<<<<< HEAD:database/migrations/2025_08_08_112218_create_faw_report_photos_table.php
        Schema::create('faw_report_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faw_report_id')->constrained()->onDelete('cascade');
            $table->string('photo_path');
            $table->timestamps();
========
        Schema::table('item_machines', function (Blueprint $table) {
            $table->string('spec')->nullable()->after('code');
>>>>>>>> temp-main:database/migrations/2025_09_09_080313_add_spec_to_item_machines_table.php
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
<<<<<<<< HEAD:database/migrations/2025_08_08_112218_create_faw_report_photos_table.php
        Schema::dropIfExists('faw_report_photos');
========
        Schema::table('item_machines', function (Blueprint $table) {
            $table->dropColumn('spec');
        });
>>>>>>>> temp-main:database/migrations/2025_09_09_080313_add_spec_to_item_machines_table.php
    }
};
