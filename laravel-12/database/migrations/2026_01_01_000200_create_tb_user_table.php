<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_user', function (Blueprint $table) {
            $table->id('id_user');
            $table->foreignId('id_outlet')->nullable()->constrained('tb_outlet', 'id_outlet')->nullOnDelete();
            $table->string('nama', 100);
            $table->string('username', 50)->unique();
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'kasir', 'owner'])->default('kasir');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_user');
    }
};