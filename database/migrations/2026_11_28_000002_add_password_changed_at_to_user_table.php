<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the user last set their own password. Empty for accounts that have
 * only signed in with a passkey (or through Kehadiran) so far: they're sent
 * to change it right away (EnsurePasswordIsSet). This database predates
 * migrations, so run only this file:
 *   php artisan migrate --path=database/migrations/2026_11_28_000002_add_password_changed_at_to_user_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->timestamp('password_changed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('password_changed_at');
        });
    }
};
