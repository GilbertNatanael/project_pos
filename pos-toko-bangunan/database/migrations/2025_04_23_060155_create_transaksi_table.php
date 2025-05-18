<?php

// database/migrations/xxxx_xx_xx_create_transaksi_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaksiTable extends Migration
{
    public function up()
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->unsignedBigInteger('id_admin');
            $table->dateTime('tanggal_waktu');
            $table->decimal('total_harga', 15, 2);
            $table->string('metode_pembayaran');
            $table->timestamps();

            $table->foreign('id_admin')->references('id_karyawan')->on('karyawan')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaksi');
    }
}

