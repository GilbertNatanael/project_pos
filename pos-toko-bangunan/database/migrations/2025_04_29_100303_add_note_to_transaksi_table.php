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
        Schema::table('transaksi', function (Blueprint $table) {
            $table->text('note')->nullable()->after('metode_pembayaran');
        });
    }
    
    public function down()
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
    
};
