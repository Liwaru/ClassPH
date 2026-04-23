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
        Schema::create('hak_akses_menu', function (Blueprint $table) {
            $table->id('id_hak_akses_menu');
            $table->unsignedTinyInteger('level');
            $table->string('menu_key');
            $table->unique(['level', 'menu_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hak_akses_menu');
    }
};
