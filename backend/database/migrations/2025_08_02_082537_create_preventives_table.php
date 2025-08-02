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
        Schema::create('preventives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_tms_id'); // Relasi ke TMS
            $table->string('foto'); // Path ke file foto
            $table->enum('status', ['before', 'after']); // Menandakan ini foto before atau after
            $table->timestamps();
    
            // Foreign key constraint
            $table->foreign('activity_tms_id')
                  ->references('id')
                  ->on('activity_tms')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preventives');
    }
};
