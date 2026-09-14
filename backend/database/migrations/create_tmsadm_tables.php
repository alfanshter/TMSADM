<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_tms', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('item_machine_id');
            $table->string('jsa_filename_cleaning_criticals')->nullable();
            $table->string('catatan_teamleader_cleaning_criticals')->nullable();
            $table->string('catatan_supervisor_cleaning_criticals')->nullable();
            $table->text('catatan_teknisi_cleaning_criticals')->nullable();
            $table->string('jsa_file_cleaning_criticals')->nullable();
            $table->string('jsa_file_just_cleaning')->nullable();
            $table->string('jsa_filename_just_cleaning')->nullable();
            $table->string('catatan_teamleader_just_cleaning')->nullable();
            $table->string('catatan_supervisor_justcleaning')->nullable();
            $table->text('catatan_teknisi_just_cleaning')->nullable();
            $table->string('jsa_file_replacement_part')->nullable();
            $table->string('jsa_filename_replacement_part')->nullable();
            $table->string('catatan_teamleader_replacement_part')->nullable();
            $table->string('catatan_supervisor_replacement_part')->nullable();
            $table->text('catatan_teknisi_replacement_part')->nullable();
            $table->string('jsa_file_preventive')->nullable();
            $table->string('jsa_filename_preventive')->nullable();
            $table->string('catatan_teamleader_preventive_pm')->nullable();
            $table->string('catatan_supervisor_preventive_pm')->nullable();
            $table->text('catatan_teknisi_preventive_pm')->nullable();
            $table->double('incoming_rs')->nullable();
            $table->double('incoming_rt')->nullable();
            $table->double('incoming_st')->nullable();
            $table->double('outgoing_rs')->nullable();
            $table->double('outgoing_rt')->nullable();
            $table->double('outgoing_st')->nullable();
            $table->string('temp')->nullable();
            $table->string('deviation')->nullable();
            $table->integer('production_downtime')->nullable();
            $table->time('start_downtime')->nullable();
            $table->time('end_downtime')->nullable();
            $table->string('production_scan')->nullable();
            $table->string('production_scan_filename')->nullable();
            $table->string('safety_scan')->nullable();
            $table->string('safety_scan_filename')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('item_machine_id');
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('cleaning_criticals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_tms_id');
            $table->string('foto');
            $table->enum('status', ['before', 'after']);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('activity_tms_id');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('faw_reports', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->date('date');
            $table->text('result');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('faw_report_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faw_report_id');
            $table->string('photo_path');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('faw_report_id');
        });

        Schema::create('item_machines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('spec')->nullable();
            $table->string('location')->nullable();
            $table->enum('scope_of_work', ['safety', 'production']);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('just_cleanings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_tms_id');
            $table->string('foto');
            $table->enum('status', ['before', 'after']);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('activity_tms_id');
        });

        Schema::create('leakage_reports', function (Blueprint $table) {
            $table->id();
            $table->string('file_scan');
            $table->string('lokasi');
            $table->date('date');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

   

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type');
            $table->unsignedBigInteger('tokenable_id');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index(['tokenable_type', 'tokenable_id']);
            $table->index('expires_at');
        });

        Schema::create('picas', function (Blueprint $table) {
            $table->id();
            $table->string('problem');
            $table->string('cause');
            $table->string('corrective_action');
            $table->date('date');
            $table->string('pic');
            $table->string('status');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('preventives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_tms_id');
            $table->string('foto');
            $table->enum('status', ['before', 'after']);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('activity_tms_id');
        });

        Schema::create('replacement_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_tms_id');
            $table->string('foto');
            $table->enum('status', ['before', 'after']);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('activity_tms_id');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            $table->index('user_id');
        });

        Schema::create('sparepart_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_sparepart_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->integer('qty')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('stock_sparepart_id');
            $table->index('user_id');
        });

        Schema::create('stock_spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sparepart');
            $table->string('spec')->nullable();
            $table->string('loc');
            $table->string('type')->nullable();
            $table->enum('category', ['Belting & House', 'Safety', 'Tools', 'Spare part & Cons']);
            $table->integer('stok')->default(0);
            $table->integer('incoming')->default(0);
            $table->string('remark');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('tmp_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->boolean('draft')->default(true);
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('user_id');
        });

        Schema::create('tms_spareparts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_tms_id');
            $table->unsignedBigInteger('stock_sparepart_id');
            $table->integer('qty');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('activity_tms_id');
            $table->index('stock_sparepart_id');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'supervisor', 'team_leader', 'teknisi']);
            $table->string('phone')->nullable();
            $table->boolean('status')->default(true);
            $table->string('avatar')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->index('created_by');
            $table->index('updated_by');
        });

        // Foreign keys are added after all tables exist.
        Schema::table('activity_tms', function (Blueprint $table) {
            $table->foreign('item_machine_id')
                ->references('id')->on('item_machines')
                ->cascadeOnDelete();
        });

        Schema::table('cleaning_criticals', function (Blueprint $table) {
            $table->foreign('activity_tms_id')
                ->references('id')->on('activity_tms')
                ->cascadeOnDelete();
        });

        Schema::table('faw_report_photos', function (Blueprint $table) {
            $table->foreign('faw_report_id')
                ->references('id')->on('faw_reports')
                ->cascadeOnDelete();
        });

        Schema::table('just_cleanings', function (Blueprint $table) {
            $table->foreign('activity_tms_id')
                ->references('id')->on('activity_tms')
                ->cascadeOnDelete();
        });

        Schema::table('preventives', function (Blueprint $table) {
            $table->foreign('activity_tms_id')
                ->references('id')->on('activity_tms')
                ->cascadeOnDelete();
        });

        Schema::table('replacement_parts', function (Blueprint $table) {
            $table->foreign('activity_tms_id')
                ->references('id')->on('activity_tms')
                ->cascadeOnDelete();
        });

        Schema::table('sparepart_logs', function (Blueprint $table) {
            $table->foreign('stock_sparepart_id')
                ->references('id')->on('stock_spareparts')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });

        Schema::table('tmp_photos', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('tms_spareparts', function (Blueprint $table) {
            $table->foreign('activity_tms_id')
                ->references('id')->on('activity_tms')
                ->cascadeOnDelete();

            $table->foreign('stock_sparepart_id')
                ->references('id')->on('stock_spareparts')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });

        Schema::table('tms_spareparts', function (Blueprint $table) {
            $table->dropForeign(['activity_tms_id']);
            $table->dropForeign(['stock_sparepart_id']);
        });

        Schema::table('tmp_photos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('sparepart_logs', function (Blueprint $table) {
            $table->dropForeign(['stock_sparepart_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('replacement_parts', function (Blueprint $table) {
            $table->dropForeign(['activity_tms_id']);
        });

        Schema::table('preventives', function (Blueprint $table) {
            $table->dropForeign(['activity_tms_id']);
        });

        Schema::table('just_cleanings', function (Blueprint $table) {
            $table->dropForeign(['activity_tms_id']);
        });

        Schema::table('faw_report_photos', function (Blueprint $table) {
            $table->dropForeign(['faw_report_id']);
        });

        Schema::table('cleaning_criticals', function (Blueprint $table) {
            $table->dropForeign(['activity_tms_id']);
        });

        Schema::table('activity_tms', function (Blueprint $table) {
            $table->dropForeign(['item_machine_id']);
        });

        Schema::dropIfExists('users');
        Schema::dropIfExists('tms_spareparts');
        Schema::dropIfExists('tmp_photos');
        Schema::dropIfExists('stock_spareparts');
        Schema::dropIfExists('sparepart_logs');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('replacement_parts');
        Schema::dropIfExists('preventives');
        Schema::dropIfExists('picas');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('migrations');
        Schema::dropIfExists('leakage_reports');
        Schema::dropIfExists('just_cleanings');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('item_machines');
        Schema::dropIfExists('faw_report_photos');
        Schema::dropIfExists('faw_reports');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('cleaning_criticals');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('activity_tms');
    }
};
