<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id_barang';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'harga_barang',
        'jumlah_barang',
        'satuan_barang',
    ];

    public $timestamps = true; // default-nya true, bisa dihilangkan kalau pakai timestamps
}
