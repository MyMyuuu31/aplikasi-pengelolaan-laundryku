<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_paket', function (Blueprint $table) {
            $table->id('id_paket');
            $table->foreignId('id_outlet')->nullable()->constrained('tb_outlet', 'id_outlet')->nullOnDelete();
            $table->string('nama_paket', 100);
            $table->enum('jenis', ['reguler', 'kilat', 'setrika', 'satuan', 'khusus']);
            $table->enum('satuan', ['kg', 'pcs', 'item']);
            $table->decimal('harga', 12, 2);
            $table->unsignedTinyInteger('estimasi_hari')->default(3);
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->index('nama_paket');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_paket');
    }
};