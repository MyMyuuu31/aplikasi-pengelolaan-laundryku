<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_detail_transaksi', function (Blueprint $table) {
            $table->id('id_detail');
            $table->foreignId('id_transaksi')->constrained('tb_transaksi', 'id_transaksi')->cascadeOnDelete();
            $table->foreignId('id_paket')->nullable()->constrained('tb_paket', 'id_paket')->nullOnDelete();
            $table->string('nama_paket', 100);
            $table->string('satuan', 10);
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('qty', 10, 2)->default(1);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();

            $table->index('id_transaksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_detail_transaksi');
    }
};