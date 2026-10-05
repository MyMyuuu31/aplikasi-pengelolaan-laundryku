<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_member', function (Blueprint $table) {
            $table->id('id_member');
            $table->foreignId('id_outlet')->nullable()->constrained('tb_outlet', 'id_outlet')->nullOnDelete();
            $table->string('kode_member', 20)->unique();
            $table->string('nama', 100);
            $table->string('no_hp', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->unsignedInteger('poin')->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->index('nama');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_member');
    }
};