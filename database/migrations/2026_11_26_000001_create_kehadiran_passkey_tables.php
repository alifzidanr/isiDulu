<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Passkey login with credentials registered in Kehadiran (presensiAppYPIA).
 * Kehadiran pushes the Direktorat TI&TD Menu TU roster and its passkey public
 * keys here (see KehadiranSyncController); both tables are replaced on every
 * push. `user.id_pegawai_kehadiran` links an isiDulu account to a pegawai.
 *
 * This database predates migrations, so run only this file:
 *   php artisan migrate --path=database/migrations/2026_11_26_000001_create_kehadiran_passkey_tables.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kehadiran_pegawai', function (Blueprint $table) {
            $table->unsignedInteger('id_pegawai')->primary();
            $table->string('nip')->nullable();
            $table->string('nama_lengkap');
            $table->string('email')->nullable();
            $table->timestamp('synced_at');
        });

        Schema::create('kehadiran_passkeys', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_pegawai')->index();
            $table->string('name');
            $table->string('credential_id')->unique();
            $table->json('credential');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('synced_at');

            $table->foreign('id_pegawai')->references('id_pegawai')->on('kehadiran_pegawai')->cascadeOnDelete();
        });

        Schema::table('user', function (Blueprint $table) {
            $table->unsignedInteger('id_pegawai_kehadiran')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('id_pegawai_kehadiran');
        });
        Schema::dropIfExists('kehadiran_passkeys');
        Schema::dropIfExists('kehadiran_pegawai');
    }
};
