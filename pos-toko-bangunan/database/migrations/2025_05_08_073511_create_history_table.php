<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('history', function (Blueprint $table) {
            $table->id('id_history');
            $table->unsignedBigInteger('id_barang')->nullable();
            $table->unsignedBigInteger('id_karyawan')->nullable();
            $table->string('aksi'); // contoh: tambah, update, delete
            $table->timestamps();

            // Optional: foreign key jika ingin
            $table->foreign('id_barang')->references('id_barang')->on('barang')->onDelete('set null');
            $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawan')->onDelete('cascade');

        });
    }

    public function down()
    {
        Schema::dropIfExists('history');
    }
}
