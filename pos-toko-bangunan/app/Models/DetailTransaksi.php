<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail_transaksi';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'id_transaksi',
        'id_barang',
        'jumlah',
        'subtotal',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }
    public function barang()
        {
         return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
        }

}

