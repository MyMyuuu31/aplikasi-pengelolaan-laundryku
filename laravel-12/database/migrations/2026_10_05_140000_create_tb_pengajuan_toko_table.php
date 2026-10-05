<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_pengajuan_toko', function (Blueprint $table) {
            $table->id('id_pengajuan');
            $table->string('nama_owner', 100);
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('nama_outlet', 100)->unique();
            $table->text('alamat');
            $table->string('telepon', 25)->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->foreignId('id_owner')->nullable()->constrained('tb_user', 'id_user')->nullOnDelete();
            $table->foreignId('id_admin')->nullable()->constrained('tb_user', 'id_user')->nullOnDelete();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_pengajuan_toko');
    }
};