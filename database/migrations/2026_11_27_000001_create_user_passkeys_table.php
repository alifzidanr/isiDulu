<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Passkeys registered on isiDulu itself (rpId = this app's host), for
 * Direktorat TI&TD members. `credential` is webauthn-lib's CredentialRecord.
 *
 * This database predates migrations, so run only this file:
 *   php artisan migrate --path=database/migrations/2026_11_27_000001_create_user_passkeys_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_passkeys', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_user')->index();
            $table->string('name');
            $table->string('credential_id')->unique();
            $table->json('credential');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_passkeys');
    }
};
