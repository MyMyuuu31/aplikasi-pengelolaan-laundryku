<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_outlet', function (Blueprint $table) {
            $table->id('id_outlet');
            $table->string('nama_outlet', 100);
            $table->text('alamat')->nullable();
            $table->string('telepon', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_outlet');
    }
};