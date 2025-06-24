<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKategoriAndMerekToBarangTable extends Migration
{
    public function up()
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->unsignedBigInteger('kategori_id')->after('nama_barang')->nullable();
            $table->string('merek')->after('kategori_id')->nullable();

            // Tambahkan foreign key ke tabel kategori jika ada
            $table->foreign('kategori_id')->references('id')->on('kategori')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn(['kategori_id', 'merek']);
        });
    }
}
