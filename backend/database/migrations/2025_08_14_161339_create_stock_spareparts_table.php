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
        Schema::create('stock_spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sparepart');
            $table->string('spec')->nullable();   // opsional
            $table->string('loc');
            $table->string('type')->nullable();   // opsional
            $table->enum('category', [
                'Belting & House',
                'Safety',
                'Tools',
                'Spare part & Cons'
            ]);
            $table->integer('stok')->default(0);
            $table->string('remark'); // pcs, unit, dll
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_spareparts');
    }
};
