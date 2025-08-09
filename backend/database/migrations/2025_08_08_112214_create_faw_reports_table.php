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
        Schema::create('faw_reports', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->date('date');
            $table->text('result'); // sama dengan description, tapi kita simpan biar fleksibel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faw_reports');
    }
};
