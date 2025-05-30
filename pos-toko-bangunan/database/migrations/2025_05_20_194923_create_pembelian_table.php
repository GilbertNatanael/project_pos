<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_pembelian_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id('id_pembelian');
            $table->date('tanggal');
            $table->integer('jumlah_item');
            $table->bigInteger('total');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('pembelian');
    }
};
