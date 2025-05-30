<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_detail_pembelian_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('detail_pembelian', function (Blueprint $table) {
            $table->id('id_detail_pembelian');
            $table->unsignedBigInteger('id_pembelian');
            $table->unsignedBigInteger('id_barang');
            $table->string('nama_barang');
            $table->integer('jumlah');
            $table->bigInteger('subtotal');
            $table->timestamps();

            // Foreign key
            $table->foreign('id_pembelian')->references('id_pembelian')->on('pembelian')->onDelete('cascade');
            $table->foreign('id_barang')->references('id_barang')->on('barang')->onDelete('restrict');
        });
    }

    public function down(): void {
        Schema::dropIfExists('detail_pembelian');
    }
};

