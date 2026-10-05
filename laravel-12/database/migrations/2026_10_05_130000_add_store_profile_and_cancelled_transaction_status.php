<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_outlet', function (Blueprint $table) {
            $table->text('deskripsi')->nullable();
        });

        Schema::table('tb_transaksi', function (Blueprint $table) {
            $table->string('status', 16)->default('proses')->change();
        });
    }

    public function down(): void
    {
        DB::table('tb_transaksi')->where('status', 'dibatalkan')->update(['status' => 'proses']);

        Schema::table('tb_transaksi', function (Blueprint $table) {
            $table->enum('status', ['proses', 'selesai', 'diambil'])->default('proses')->change();
        });

        Schema::table('tb_outlet', function (Blueprint $table) {
            $table->dropColumn('deskripsi');
        });
    }
};