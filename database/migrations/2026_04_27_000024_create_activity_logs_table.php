<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->string('user_name')->nullable();
            $table->unsignedTinyInteger('user_level')->nullable();
            $table->string('role_name')->nullable();
            $table->string('action', 80);
            $table->string('module', 120);
            $table->string('target', 255)->nullable();
            $table->text('detail')->nullable();
            $table->string('room_context', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->index(['user_level', 'created_at']);
            $table->index(['action', 'module']);
            $table->index('user_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
