<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->string('no_invoice', 30)->unique();
            $table->foreignId('id_outlet')->nullable()->constrained('tb_outlet', 'id_outlet')->nullOnDelete();
            $table->foreignId('id_member')->nullable()->constrained('tb_member', 'id_member')->nullOnDelete();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->decimal('total_harga', 14, 2)->default(0);
            $table->decimal('diskon', 14, 2)->default(0);
            $table->decimal('diskon_persen', 5, 2)->default(0);
            $table->decimal('pajak_persen', 5, 2)->default(0);
            $table->decimal('pajak', 14, 2)->default(0);
            $table->decimal('total_bayar', 14, 2)->default(0);
            $table->decimal('uang_diterima', 14, 2)->default(0);
            $table->decimal('total_kembali', 14, 2)->default(0);
            $table->enum('metode_bayar', ['tunai', 'transfer', 'qris'])->default('tunai');
            $table->enum('status', ['proses', 'selesai', 'diambil'])->default('proses');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('tb_user')->nullOnDelete();
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_transaksi');
    }
};