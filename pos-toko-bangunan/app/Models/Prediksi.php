<?php

// app/Models/Prediksi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediksi extends Model
{
    protected $table = 'prediksi';
    protected $primaryKey = 'id_prediksi';
    
    protected $fillable = [
        'tanggal',
        'jumlah_item',
        'jumlah_hari'
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];

    public function detailPrediksi()
    {
        return $this->hasMany(DetailPrediksi::class, 'id_prediksi', 'id_prediksi');
    }

    public function dataPrediksi()
    {
        return $this->hasMany(DataPrediksi::class, 'id_prediksi', 'id_prediksi');
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