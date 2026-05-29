<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah enum value baru 'teknisi'
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'supervisor', 'team_leader', 'teknisi') NOT NULL");
    }

    public function down(): void
    {
        // Rollback ke enum lama
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'supervisor', 'team_leader') NOT NULL");
    }
};
