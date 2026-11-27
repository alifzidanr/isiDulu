<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Users may have no kampus (and no unit): they see every permohonan but
 * can't act on them (User::isReadOnly()). This database predates
 * migrations, so run only this file:
 *   php artisan migrate --path=database/migrations/2026_11_28_000003_make_user_id_kampus_nullable.php
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "user" ALTER COLUMN id_kampus DROP NOT NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "user" ALTER COLUMN id_kampus SET NOT NULL');
        }
    }
};
