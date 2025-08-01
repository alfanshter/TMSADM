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
        Schema::create('maintenance_types', function (Blueprint $table) {
            $table->id();
            $table->enum('maintenance_type', [
                'Cleaning Critical',
                'Just Cleaning',
                'Replacement Part',
                'Preventive (PM)',
            ]);
            $table->foreignId('tms_id')->constrained('activity_tms')->onDelete('cascade');
            $table->string('foto_before_maintenance')->nullable();
            $table->string('foto_after_maintenance')->nullable();
            $table->string('jsa_file_maintenance')->nullable();
             // file pdf/docx
            $table->string('foto_before_cleaning')->nullable();
            $table->string('foto_after_cleaning')->nullable();
            $table->string('jsa_file_cleaning')->nullable(); // file pdf/docx

            $table->string('foto_before_just_cleaning')->nullable();
            $table->string('foto_after_just_cleaning')->nullable();
            $table->string('jsa_file_just_cleaning')->nullable(); // file pdf/docx

            $table->string('foto_before_replacement')->nullable();
            $table->string('foto_after_replacement')->nullable();
            $table->string('jsa_file_replacement')->nullable(); // file pdf/docx

            $table->string('foto_before_pm')->nullable();
            $table->string('foto_after_pm')->nullable();
            $table->string('jsa_file_pm')->nullable(); // file pdf/docx
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_types');
    }
};
