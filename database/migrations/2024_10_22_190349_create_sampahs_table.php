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
        Schema::create('sampah', function (Blueprint $table) {
            $table->uuid('id_sampah')->primary();
            $table->string('nama_sampah');
            $table->decimal('berat_sampah', 8, 2);
            $table->integer('point');
            $table->uuid('id_jenis')->foreign('id_jenis')->references('id_jenis')->on('jenis_sampah')->cascadeOnDelete();
            $table->uuid('id_penjemputan')->foreign('id_penjemputan')->references('id_penjemputan')->on('penjemputan_sampah')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sampah');
    }
};
