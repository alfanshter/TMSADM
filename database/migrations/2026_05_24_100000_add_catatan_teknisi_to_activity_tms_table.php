<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_tms', function (Blueprint $table) {
            $table->text('catatan_teknisi_cleaning_criticals')->nullable()->after('catatan_supervisor_cleaning_criticals');
            $table->text('catatan_teknisi_just_cleaning')->nullable()->after('catatan_supervisor_justcleaning');
            $table->text('catatan_teknisi_replacement_part')->nullable()->after('catatan_supervisor_replacement_part');
            $table->text('catatan_teknisi_preventive_pm')->nullable()->after('catatan_supervisor_preventive_pm');
        });
    }

    public function down(): void
    {
        Schema::table('activity_tms', function (Blueprint $table) {
            $table->dropColumn([
                'catatan_teknisi_cleaning_criticals',
                'catatan_teknisi_just_cleaning',
                'catatan_teknisi_replacement_part',
                'catatan_teknisi_preventive_pm',
            ]);
        });
    }
};
