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
        Schema::table('activity_tms', function (Blueprint $table) {
            $table->string('catatan_teamleader_cleaning_criticals')->nullable()->after('jsa_filename_cleaning_criticals');
            $table->string('catatan_supervisor_cleaning_criticals')->nullable()->after('catatan_teamleader_cleaning_criticals');
            $table->string('catatan_teamleader_just_cleaning')->nullable()->after('jsa_filename_just_cleaning');
            $table->string('catatan_supervisor_justcleaning')->nullable()->after('catatan_teamleader_just_cleaning');
            $table->string('catatan_teamleader_replacement_part')->nullable()->after('jsa_filename_replacement_part');
            $table->string('catatan_supervisor_replacement_part')->nullable()->after('catatan_teamleader_replacement_part');
            $table->string('catatan_teamleader_preventive_pm')->nullable()->after('jsa_filename_preventive');
            $table->string('catatan_supervisor_preventive_pm')->nullable()->after('catatan_teamleader_preventive_pm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_tms', function (Blueprint $table) {
            $table->dropColumn([
                'catatan_teamleader_cleaning_criticals',
                'catatan_supervisor_cleaning_criticals',
                'catatan_teamleader_just_cleaning',
                'catatan_supervisor_justcleaning',
                'catatan_teamleader_replacement_part',
                'catatan_supervisor_replacement_part',
                'catatan_teamleader_preventive_pm',
                'catatan_supervisor_preventive_pm',
            ]);
        });
    }
};
