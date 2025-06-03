<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prediksi', function (Blueprint $table) {
            // Hapus kolom jumlah_hari
            $table->dropColumn('jumlah_hari');
            
            // Tambah kolom tanggal_dari dan tanggal_sampai
            $table->date('tanggal_dari')->after('jumlah_item');
            $table->date('tanggal_sampai')->after('tanggal_dari');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prediksi', function (Blueprint $table) {
            // Kembalikan kolom jumlah_hari
            $table->integer('jumlah_hari')->after('jumlah_item');
            
            // Hapus kolom tanggal_dari dan tanggal_sampai
            $table->dropColumn(['tanggal_dari', 'tanggal_sampai']);
        });
    }
};