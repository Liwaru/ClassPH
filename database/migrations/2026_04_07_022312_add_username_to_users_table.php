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
        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->after('nama');
            });
        } else {
            DB::statement('ALTER TABLE users MODIFY username VARCHAR(255) NULL');
        }

        DB::table('users')
            ->where('username', '')
            ->update(['username' => null]);

        $hasUniqueIndex = collect(DB::select("
            SELECT index_name
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'users'
              AND index_name = 'users_username_unique'
        "))->isNotEmpty();

        if (! $hasUniqueIndex) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('username');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            $hasUniqueIndex = collect(DB::select("
                SELECT index_name
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = 'users'
                  AND index_name = 'users_username_unique'
            "))->isNotEmpty();

            Schema::table('users', function (Blueprint $table) use ($hasUniqueIndex) {
                if ($hasUniqueIndex) {
                    $table->dropUnique('users_username_unique');
                }

                $table->dropColumn('username');
            });
        }
    }
};
