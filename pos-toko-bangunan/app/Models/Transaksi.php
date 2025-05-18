<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'id_admin',
        'tanggal_waktu',
        'total_harga',
        'metode_pembayaran',
        'note'
    ];

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }
}
