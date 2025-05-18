<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pembelian', function (Blueprint $table) {
        $table->id('id_pembelian');
        $table->timestamp('tanggal_waktu')->useCurrent();
        $table->timestamps();
        });

    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};
