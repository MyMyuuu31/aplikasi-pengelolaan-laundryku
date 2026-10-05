<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_log', function (Blueprint $table) {
            $table->id('id_log');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->string('nama_user', 100)->nullable();
            $table->string('role', 20)->nullable();
            $table->string('modul', 50)->nullable();
            $table->string('aksi', 50);
            $table->string('deskripsi', 255);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('tb_user')->nullOnDelete();
            $table->index(['aksi', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_log');
    }
};