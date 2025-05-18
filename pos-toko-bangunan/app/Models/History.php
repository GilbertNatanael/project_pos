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
        'aksi'
    ];

    public $timestamps = true;

    // Relasi dengan model Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    // Relasi dengan model Karyawan
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }
}

