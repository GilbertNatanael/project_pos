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
    Schema::table('detail_prediksi', function (Blueprint $table) {
        $table->integer('sisa_hari')->nullable()->change();
        $table->date('tanggal_habis')->nullable()->change();
    });
}

public function down()
{
    Schema::table('detail_prediksi', function (Blueprint $table) {
        $table->integer('sisa_hari')->nullable(false)->change();
        $table->date('tanggal_habis')->nullable(false)->change();
    });
}

};
