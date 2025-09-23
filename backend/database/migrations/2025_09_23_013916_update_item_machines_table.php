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
        Schema::table('item_machines', function (Blueprint $table) {
            // ubah column
            $table->string('code')->nullable()->change();
            $table->string('location')->nullable()->change();
        });
    }
    
    public function down(): void
    {
        Schema::table('item_machines', function (Blueprint $table) {
            $table->string('code')->unique()->change();
            $table->string('location')->nullable(false)->change();
        });
    }
    
};
