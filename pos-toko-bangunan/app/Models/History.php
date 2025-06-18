<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'history';
    protected $primaryKey = 'id_history';

    protected $fillable = [
        'id_barang',
        'id_karyawan',
        'aksi',
        'nama_barang'
    ];


    public $timestamps = true;

    // Relasi dengan model Barang - dengan foreign key dan local key yang eksplisit
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    // Relasi dengan model Karyawan - dengan foreign key dan local key yang eksplisit
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }
}