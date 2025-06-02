<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterJumlahPrediksiToDecimalInDataPrediksiTable extends Migration
{
    public function up()
    {
        Schema::table('data_prediksi', function (Blueprint $table) {
            $table->decimal('jumlah_prediksi', 10, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('data_prediksi', function (Blueprint $table) {
            $table->integer('jumlah_prediksi')->change();
        });
    }
}
