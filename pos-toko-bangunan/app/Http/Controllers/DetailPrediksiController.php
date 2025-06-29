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

            // Hitung reorder point
            $reorderData = $this->calculateReorderPoint($detail);

            // Format stok tersisa berdasarkan satuan
            $stokTersisaFormatted = $this->formatJumlahBySatuan($detail->stok_tersisa, $satuanBarang);

            $itemData = [
                'nama_item' => $detail->nama_item,
                'satuan_barang' => $satuanBarang,
                'stok_tersisa' => $stokTersisaFormatted,
                'sisa_hari' => $detail->sisa_hari,
                'tanggal_habis' => $detail->tanggal_habis ? $detail->tanggal_habis->format('Y-m-d') : null,
                // PERBAIKAN: Pastikan data reorder ada
                'reorder_point' => $this->formatJumlahBySatuan($reorderData['reorder_point'], $satuanBarang),
                'order_quantity' => $this->formatJumlahBySatuan($reorderData['order_quantity'], $satuanBarang),
                'lead_time_stock' => $this->formatJumlahBySatuan($reorderData['lead_time_stock'], $satuanBarang),
                'safety_stock' => $this->formatJumlahBySatuan($reorderData['safety_stock'], $satuanBarang),
                'konsumsi_per_hari' => $reorderData['konsumsi_per_hari'],
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
                    'bulan' => $data->tanggal->format('Y-m'), // Tambahkan format bulan
                    'jumlah' => $jumlahPrediksiFormatted
                ];

                // Cari data aktual transaksi untuk bulan yang sama (PERUBAHAN UTAMA)
                $aktualData = $this->getAktualDataBulanan($detail->nama_item, $data->tanggal);
                
                // Format aktual data jika ada
                if ($aktualData !== null) {
                    $aktualData = $this->formatJumlahBySatuan($aktualData, $satuanBarang);
                }
                
                $itemData['aktual_data'][] = [
                    'tanggal' => $data->tanggal->format('Y-m-d'),
                    'bulan' => $data->tanggal->format('Y-m'), // Tambahkan format bulan
                    'jumlah' => $aktualData
                ];
            }

            $chartData[] = $itemData;
        }

        // PERBAIKAN: Tambahkan satuan ke detailPrediksi dan hitung reorder point untuk setiap item
        foreach ($detailPrediksi as $detail) {
            $barang = Barang::where('nama_barang', $detail->nama_item)->first();
            $detail->satuan_barang = $barang ? $barang->satuan_barang : 'unit';
            
            // Format stok tersisa untuk tampilan tabel
            $detail->stok_tersisa_formatted = $this->formatJumlahBySatuan($detail->stok_tersisa, $detail->satuan_barang);
            
            // Tambahkan reorder point data
            $reorderData = $this->calculateReorderPoint($detail);
            $detail->reorder_point = $this->formatJumlahBySatuan($reorderData['reorder_point'], $detail->satuan_barang);
            $detail->order_quantity = $this->formatJumlahBySatuan($reorderData['order_quantity'], $detail->satuan_barang);
            $detail->lead_time_stock = $this->formatJumlahBySatuan($reorderData['lead_time_stock'], $detail->satuan_barang);
            $detail->safety_stock = $this->formatJumlahBySatuan($reorderData['safety_stock'], $detail->satuan_barang);
            
            // Status reorder
            if ($detail->stok_tersisa <= $reorderData['reorder_point']) {
                $detail->reorder_status = 'perlu_pesan';
            } else {
                $detail->reorder_status = 'aman';
            }
        }

        return view('detail_prediksi', compact('prediksi', 'detailPrediksi', 'chartData'));
    }

    // FUNGSI BARU: Ambil data aktual berdasarkan bulan (bukan hari)
    private function getAktualDataBulanan($namaItem, $tanggal)
    {
        // Cari barang berdasarkan nama
        $barang = Barang::where('nama_barang', $namaItem)->first();
        
        if (!$barang) {
            return null;
        }

        // Ambil tahun dan bulan dari tanggal
        $tahun = $tanggal->year;
        $bulan = $tanggal->month;

        // Cari semua transaksi dalam bulan tersebut dan jumlahkan
        $totalJumlah = DetailTransaksi::whereHas('transaksi', function($query) use ($tahun, $bulan) {
                $query->whereYear('tanggal_waktu', $tahun)
                      ->whereMonth('tanggal_waktu', $bulan);
            })
            ->where('id_barang', $barang->id_barang)
            ->sum('jumlah');

        return $totalJumlah > 0 ? $totalJumlah : null;
    }

    // FUNGSI LAMA: Tetap dipertahankan untuk kompatibilitas
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
                // PERUBAHAN: Gunakan data aktual bulanan
                $aktualData = $this->getAktualDataBulanan($detail->nama_item, $data->tanggal);
                
                // Format data berdasarkan satuan
                $prediksiFormatted = $this->formatJumlahBySatuan($data->jumlah_prediksi, $satuanBarang);
                $aktualFormatted = $aktualData !== null ? $this->formatJumlahBySatuan($aktualData, $satuanBarang) : null;
                
                $itemData['data'][] = [
                    'tanggal' => $data->tanggal->format('Y-m-d'),
                    'bulan' => $data->tanggal->format('Y-m'), // Tambahkan format bulan
                    'prediksi' => $prediksiFormatted,
                    'aktual' => $aktualFormatted
                ];
            }

            $chartData[] = $itemData;
        }

        return response()->json($chartData);
    }

    /**
     * Hitung reorder point berdasarkan konsumsi prediksi
     * Lead time: 2 minggu, Order untuk: 1 bulan
     */
    private function calculateReorderPoint($detail)
    {
        // Ambil rata-rata konsumsi per hari dari data prediksi
        $totalKonsumsi = 0;
        $jumlahPeriode = 0;
        
        foreach ($detail->dataPrediksi as $data) {
            $totalKonsumsi += $data->jumlah_prediksi;
            $jumlahPeriode++;
        }
        
        if ($jumlahPeriode == 0) {
            return [
                'reorder_point' => 0,
                'order_quantity' => 0,
                'lead_time_stock' => 0,
                'safety_stock' => 0,
                'konsumsi_per_hari' => 0
            ];
        }
        
        // Rata-rata konsumsi per bulan
        $rataKonsumsiPerBulan = $totalKonsumsi / $jumlahPeriode;
        
        // Konsumsi per hari (asumsi 30 hari per bulan)
        $konsumsiPerHari = $rataKonsumsiPerBulan / 30;
        
        // Lead time stock (2 minggu = 14 hari)
        $leadTimeStock = $konsumsiPerHari * 14;
        
        // Order quantity (1 bulan = 30 hari)
        $orderQuantity = $konsumsiPerHari * 30;
        
        // Reorder Point = Lead Time Stock + Safety Stock (10% dari order quantity)
        $safetyStock = $orderQuantity * 0.1;
        $reorderPoint = $leadTimeStock + $safetyStock;
        
        return [
            'reorder_point' => round($reorderPoint, 1),
            'order_quantity' => round($orderQuantity, 1),
            'lead_time_stock' => round($leadTimeStock, 1),
            'safety_stock' => round($safetyStock, 1),
            'konsumsi_per_hari' => round($konsumsiPerHari, 2)
        ];
    }
}