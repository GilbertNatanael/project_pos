<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prediksi;
use App\Models\DetailPrediksi;
use App\Models\DataPrediksi;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Barang;
use Carbon\Carbon;

class DetailPrediksiController extends Controller
{
    /**
     * Format jumlah berdasarkan satuan barang
     */
    private function formatJumlahBySatuan($jumlah, $satuan)
    {
        if (strtoupper($satuan) === 'KG') {
            return round($jumlah, 1); // 1 angka di belakang koma untuk KG
        } else {
            return round($jumlah, 0); // Tanpa desimal untuk satuan lain
        }
    }

    public function show($id_prediksi)
    {
        $prediksi = Prediksi::with('detailPrediksi.dataPrediksi')
            ->findOrFail($id_prediksi);

        $detailPrediksi = $prediksi->detailPrediksi;
        
        // Siapkan data untuk grafik
        $chartData = [];
        
        foreach ($detailPrediksi as $detail) {
            // Ambil data barang untuk mendapatkan satuan
            $barang = Barang::where('nama_barang', $detail->nama_item)->first();
            $satuanBarang = $barang ? $barang->satuan_barang : 'unit';
            
            // Format stok tersisa berdasarkan satuan
            $stokTersisaFormatted = $this->formatJumlahBySatuan($detail->stok_tersisa, $satuanBarang);
            
            $itemData = [
                'nama_item' => $detail->nama_item,
                'satuan_barang' => $satuanBarang,
                'stok_tersisa' => $stokTersisaFormatted,
                'sisa_hari' => $detail->sisa_hari,
                'tanggal_habis' => $detail->tanggal_habis ? $detail->tanggal_habis->format('Y-m-d') : null,
                'prediksi_data' => [],
                'aktual_data' => []
            ];

            // Ambil data prediksi
            $dataPrediksi = $detail->dataPrediksi()
                ->orderBy('tanggal', 'asc')
                ->get();

            foreach ($dataPrediksi as $data) {
                // Format jumlah prediksi berdasarkan satuan
                $jumlahPrediksiFormatted = $this->formatJumlahBySatuan($data->jumlah_prediksi, $satuanBarang);
                
                $itemData['prediksi_data'][] = [
                    'tanggal' => $data->tanggal->format('Y-m-d'),
                    'jumlah' => $jumlahPrediksiFormatted
                ];

                // Cari data aktual transaksi untuk tanggal yang sama
                $aktualData = $this->getAktualData($detail->nama_item, $data->tanggal);
                
                // Format aktual data jika ada
                if ($aktualData !== null) {
                    $aktualData = $this->formatJumlahBySatuan($aktualData, $satuanBarang);
                }
                
                $itemData['aktual_data'][] = [
                    'tanggal' => $data->tanggal->format('Y-m-d'),
                    'jumlah' => $aktualData
                ];
            }

            $chartData[] = $itemData;
        }

        // Tambahkan satuan ke detailPrediksi untuk ditampilkan di tabel dan format stok tersisa
        foreach ($detailPrediksi as $detail) {
            $barang = Barang::where('nama_barang', $detail->nama_item)->first();
            $detail->satuan_barang = $barang ? $barang->satuan_barang : 'unit';
            
            // Format stok tersisa untuk tampilan tabel
            $detail->stok_tersisa_formatted = $this->formatJumlahBySatuan($detail->stok_tersisa, $detail->satuan_barang);
        }

        return view('detail_prediksi', compact('prediksi', 'detailPrediksi', 'chartData'));
    }

    private function getAktualData($namaItem, $tanggal)
    {
        // Cari barang berdasarkan nama
        $barang = Barang::where('nama_barang', $namaItem)->first();
        
        if (!$barang) {
            return null;
        }

        // Cari transaksi pada tanggal tersebut
        $totalJumlah = DetailTransaksi::whereHas('transaksi', function($query) use ($tanggal) {
                $query->whereDate('tanggal_waktu', $tanggal);
            })
            ->where('id_barang', $barang->id_barang)
            ->sum('jumlah');

        return $totalJumlah > 0 ? $totalJumlah : null;
    }

    public function getData($id_prediksi)
    {
        $prediksi = Prediksi::with('detailPrediksi.dataPrediksi')
            ->findOrFail($id_prediksi);

        $chartData = [];
        
        foreach ($prediksi->detailPrediksi as $detail) {
            // Ambil satuan barang
            $barang = Barang::where('nama_barang', $detail->nama_item)->first();
            $satuanBarang = $barang ? $barang->satuan_barang : 'unit';
            
            $itemData = [
                'nama_item' => $detail->nama_item,
                'satuan_barang' => $satuanBarang,
                'data' => []
            ];

            $dataPrediksi = $detail->dataPrediksi()
                ->orderBy('tanggal', 'asc')
                ->get();

            foreach ($dataPrediksi as $data) {
                $aktualData = $this->getAktualData($detail->nama_item, $data->tanggal);
                
                // Format data berdasarkan satuan
                $prediksiFormatted = $this->formatJumlahBySatuan($data->jumlah_prediksi, $satuanBarang);
                $aktualFormatted = $aktualData !== null ? $this->formatJumlahBySatuan($aktualData, $satuanBarang) : null;
                
                $itemData['data'][] = [
                    'tanggal' => $data->tanggal->format('Y-m-d'),
                    'prediksi' => $prediksiFormatted,
                    'aktual' => $aktualFormatted
                ];
            }

            $chartData[] = $itemData;
        }

        return response()->json($chartData);
    }
}