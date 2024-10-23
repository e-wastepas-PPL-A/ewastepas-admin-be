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
        Schema::create('penjemputan_sampah', function (Blueprint $table) {
            $table->uuid('id_penjemputan')->primary();
            $table->decimal('jumlah_sampah', 8, 2);
            $table->date('tanggal_penjemputan');
            $table->string('alamat_penjemputan');
            $table->string('status_penjemputan')->default('pending');
            $table->string('status_pengiriman')->default('belum dikirim');
            $table->decimal('total_sampah', 8, 2)->default(0);
            $table->uuid('id_user')->foreign('id_user')->references('id_user')->on('users');
            $table->uuid('id_dropbox')->foreign('id_dropbox')->references('id_dropbox')->on('dropboxes')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjemputan_sampah');
    }
};
