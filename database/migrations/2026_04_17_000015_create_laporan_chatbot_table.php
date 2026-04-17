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
        Schema::create('laporan_chatbot', function (Blueprint $table) {
            $table->id('id_laporan_chatbot');
            $table->unsignedBigInteger('id_user_pelapor');
            $table->unsignedBigInteger('id_user_tujuan')->nullable();
            $table->string('topik')->default('Lainnya');
            $table->text('pesan');
            $table->string('status')->default('baru');
            $table->dateTime('dibuat_pada');
            $table->dateTime('ditindaklanjuti_pada')->nullable();

            $table->foreign('id_user_pelapor')->references('id_user')->on('users')->cascadeOnDelete();
            $table->foreign('id_user_tujuan')->references('id_user')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_chatbot');
    }
};
