<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPembelian extends Model
{
    protected $table = 'detail_pembelian';
    protected $primaryKey = 'id_detail';
    protected $fillable = ['id_pembelian', 'id_barang', 'jumlah'];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }
}


