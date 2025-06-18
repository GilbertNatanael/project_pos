<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNamaBarangToHistoryTable extends Migration
{
    public function up()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->string('nama_barang')->nullable()->after('id_barang');
        });
    }

    public function down()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->dropColumn('nama_barang');
        });
    }
}
