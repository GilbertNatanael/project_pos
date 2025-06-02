<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('detail_prediksi', function (Blueprint $table) {
        $table->id('id_detail_prediksi');
        $table->unsignedBigInteger('id_prediksi');
        $table->string('nama_item');
        $table->integer('stok_tersisa');
        $table->integer('sisa_hari');
        $table->date('tanggal_habis')->nullable();
        $table->timestamps();

        $table->foreign('id_prediksi')->references('id_prediksi')->on('prediksi')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_prediksi');
    }
};
