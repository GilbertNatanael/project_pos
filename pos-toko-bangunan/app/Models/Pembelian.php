<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';
    protected $fillable = [];

    public function detail()
    {
        return $this->hasMany(DetailPembelian::class, 'id_pembelian');
    }
}


