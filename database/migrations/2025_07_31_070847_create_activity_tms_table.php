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
            $table->string('jsa_filename_cleaning_criticals')->nullable();
            $table->string('jsa_file_cleaning_criticals')->nullable();
            $table->string('jsa_file_just_cleaning')->nullable();
            $table->string('jsa_filename_just_cleaning')->nullable();
            $table->string('jsa_file_replacement_part')->nullable();
            $table->string('jsa_filename_replacement_part')->nullable();
            $table->string('jsa_file_preventive')->nullable();
            $table->string('jsa_filename_preventive')->nullable();
            $table->float('incoming_rs')->nullable();
            $table->float('incoming_rt')->nullable();
            $table->float('incoming_st')->nullable();
            $table->float('outgoing_rs')->nullable();
            $table->float('outgoing_rt')->nullable();
            $table->float('outgoing_st')->nullable();
            $table->string('temp')->nullable();
            $table->string('deviation')->nullable();
            $table->integer('production_downtime')->nullable();
            $table->string('production_scan')->nullable();
            $table->string('production_scan_filename')->nullable();
            $table->string('safety_scan')->nullable();
            $table->string('safety_scan_filename')->nullable();
            $table->timestamps();
<<<<<<< HEAD
=======
            $table->string('catatan_teamleader_cleaning_criticals')->nullable();
            $table->string('catatan_supervisor_cleaning_criticals')->nullable();
            $table->string('catatan_teamleader_just_cleaning')->nullable();
            $table->string('catatan_supervisor_justcleaning')->nullable();
            $table->string('catatan_teamleader_replacement_part')->nullable();
            $table->string('catatan_supervisor_replacement_part')->nullable();
            $table->string('catatan_teamleader_preventive_pm')->nullable();
            $table->string('catatan_supervisor_preventive_pm')->nullable();
>>>>>>> temp-main
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
