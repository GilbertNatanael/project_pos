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
        'merek',
        'kategori_id'
    ];

    public $timestamps = true; // default-nya true, bisa dihilangkan kalau pakai timestamps

        public function histories()
    {
        return $this->hasMany(History::class, 'id_barang', 'id_barang');
    }
    public function kategori()
{
    return $this->belongsTo(Kategori::class, 'kategori_id');
}

}
