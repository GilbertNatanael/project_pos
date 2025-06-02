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
    Schema::create('data_prediksi', function (Blueprint $table) {
        $table->id('id_data_prediksi');
        $table->unsignedBigInteger('id_detail_prediksi');
        $table->unsignedBigInteger('id_prediksi');
        $table->date('tanggal');
        $table->decimal('jumlah_prediksi', 10, 2); // Ganti dari integer ke decimal
        $table->timestamps();

        $table->foreign('id_detail_prediksi')->references('id_detail_prediksi')->on('detail_prediksi')->onDelete('cascade');
        $table->foreign('id_prediksi')->references('id_prediksi')->on('prediksi')->onDelete('cascade');
    });
}



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_prediksi');
    }
};
