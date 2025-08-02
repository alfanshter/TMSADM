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
        Schema::create('activity_tms', function (Blueprint $table) {
        $table->id();
        $table->date('date');
        $table->foreignId('item_machine_id')->constrained('item_machines')->onDelete('cascade');
        $table->string('jsa_file_cleaning_criticals')->nullable();      
        $table->string('jsa_file_just_cleaning')->nullable();      
        $table->string('jsa_file_replacement_part')->nullable();      
        $table->string('jsa_file_preventive')->nullable();      
        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_tms');
    }
};
