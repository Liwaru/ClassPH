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
        $driver = Schema::getConnection()->getDriverName();

        if (! Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table) {
                $emailColumn = $table->string('email')->nullable();

                if (Schema::hasColumn('users', 'username')) {
                    $emailColumn->after('username');
                    return;
                }

                $emailColumn->after('nama');
            });
        }

        DB::table('users')
            ->where('email', '')
            ->update(['email' => null]);

        $hasUniqueIndex = $driver === 'mysql'
            ? collect(DB::select("
                SELECT index_name
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = 'users'
                  AND index_name = 'users_email_unique'
            "))->isNotEmpty()
            : false;

        if (! $hasUniqueIndex) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'email')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        $hasUniqueIndex = $driver === 'mysql'
            ? collect(DB::select("
                SELECT index_name
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = 'users'
                  AND index_name = 'users_email_unique'
            "))->isNotEmpty()
            : true;

        Schema::table('users', function (Blueprint $table) use ($hasUniqueIndex) {
            if ($hasUniqueIndex) {
                $table->dropUnique('users_email_unique');
            }

            $table->dropColumn('email');
        });
    }
};
