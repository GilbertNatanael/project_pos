<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediksi extends Model
{
    use HasFactory;

    protected $table = 'prediksi';
    protected $primaryKey = 'id_prediksi';
    
    protected $fillable = [
        'tanggal',
        'jumlah_item', 
        'tanggal_dari',
        'tanggal_sampai'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_dari' => 'date', 
        'tanggal_sampai' => 'date'
    ];

    public function detailPrediksi()
    {
        return $this->hasMany(DetailPrediksi::class, 'id_prediksi', 'id_prediksi');
    }

    public function dataPrediksi()
    {
        return $this->hasMany(DataPrediksi::class, 'id_prediksi', 'id_prediksi');
    }

    // Accessor untuk menampilkan bulan saja
    public function getBulanDariAttribute()
    {
        return $this->tanggal_dari ? $this->tanggal_dari->format('Y-m') : null;
    }

    public function getBulanSampaiAttribute()
    {
        return $this->tanggal_sampai ? $this->tanggal_sampai->format('Y-m') : null;
    }
}
// app/Models/DetailPrediksi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPrediksi extends Model
{
    protected $table = 'detail_prediksi';
    protected $primaryKey = 'id_detail_prediksi';
    
    protected $fillable = [
        'id_prediksi',
        'nama_item',
        'stok_tersisa',
        'sisa_hari',
        'tanggal_habis'
    ];

    protected $casts = [
        'tanggal_habis' => 'date'
    ];

    public function prediksi()
    {
        return $this->belongsTo(Prediksi::class, 'id_prediksi', 'id_prediksi');
    }

    public function dataPrediksi()
    {
        return $this->hasMany(DataPrediksi::class, 'id_detail_prediksi', 'id_detail_prediksi');
    }
}

// app/Models/DataPrediksi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPrediksi extends Model
{
    public $timestamps = false;
    protected $table = 'data_prediksi';
    protected $primaryKey = 'id_data_prediksi';
    
    protected $fillable = [
        'id_detail_prediksi',
        'id_prediksi',
        'tanggal',
        'jumlah_prediksi'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah_prediksi' => 'decimal:2'
    ];

    public function prediksi()
    {
        return $this->belongsTo(Prediksi::class, 'id_prediksi', 'id_prediksi');
    }

    public function detailPrediksi()
    {
        return $this->belongsTo(DetailPrediksi::class, 'id_detail_prediksi', 'id_detail_prediksi');
    }
}