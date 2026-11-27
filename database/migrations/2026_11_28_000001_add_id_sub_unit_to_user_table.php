<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A user's sub unit (optional, must belong to their unit). This database
 * predates migrations, so run only this file:
 *   php artisan migrate --path=database/migrations/2026_11_28_000001_add_id_sub_unit_to_user_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->unsignedInteger('id_sub_unit')->nullable();
            $table->foreign('id_sub_unit')->references('id_sub_unit')->on('sub_unit')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropForeign(['id_sub_unit']);
            $table->dropColumn('id_sub_unit');
        });
    }
};
