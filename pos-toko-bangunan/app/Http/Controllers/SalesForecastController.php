<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Client;
use App\Models\Barang;
use App\Models\Prediksi;
use App\Models\DetailPrediksi;
use App\Models\DataPrediksi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesForecastController extends Controller
{
    private $flaskApiUrl = 'http://localhost:5000'; // Your Flask API URL
    
    private $items = [
        'Besi beton 6 MM ASLI SNI',
        'Besi beton 8 MM ASLI SNI',
        'PAKU 10 CM(4")',
        'PAKU 7 CM(3")',
        'Pipa Galv',
        'SENG GEL KALISCO 0,20',
        'Semen Kupang'
    ];

    public function index()
    {
        return view('forecast.index', ['items' => $this->items]);
    }

    public function predictSingle(Request $request): JsonResponse
    {
        $request->validate([
            'item_name' => 'required|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from'
        ]);

        // Validasi periode yang overlapping
        $overlapCheck = $this->checkPeriodOverlap($request->item_name, $request->date_from, $request->date_to);
        if ($overlapCheck['has_overlap']) {
            return response()->json([
                'error' => 'Periode prediksi bertabrakan dengan prediksi sebelumnya',
                'details' => $overlapCheck['details']
            ], 422);
        }

        try {
            $client = new Client();
            $response = $client->post($this->flaskApiUrl . '/predict', [
                'json' => [
                    'item_name' => $request->item_name,
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to
                ],
                'timeout' => 30
            ]);

            $data = json_decode($response->getBody(), true);
            
            // Tambahkan informasi periode
            $data['date_from'] = $request->date_from;
            $data['date_to'] = $request->date_to;
            
            // Tambahkan perhitungan kapan barang habis (bulanan)
            $stockInfo = $this->calculateMonthlyStockDepletion($request->item_name, $data['predictions']);
            $data['stock_info'] = $stockInfo;
            
            // Simpan data prediksi ke database
            DB::transaction(function () use ($request, $data, $stockInfo) {
                $this->saveSinglePrediction($request, $data, $stockInfo);
            });
            
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Prediction service unavailable: ' . $e->getMessage()
            ], 500);
        }
    }

    public function predictAll(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from'
        ]);

        // Validasi periode yang overlapping untuk semua item
        $overlapResults = [];
        foreach ($this->items as $item) {
            $overlapCheck = $this->checkPeriodOverlap($item, $request->date_from, $request->date_to);
            if ($overlapCheck['has_overlap']) {
                $overlapResults[] = [
                    'item' => $item,
                    'details' => $overlapCheck['details']
                ];
            }
        }

        if (!empty($overlapResults)) {
            return response()->json([
                'error' => 'Beberapa item memiliki periode prediksi yang bertabrakan',
                'overlapping_items' => $overlapResults
            ], 422);
        }

        try {
            $client = new Client();
            $response = $client->post($this->flaskApiUrl . '/predict/all', [
                'json' => [
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to
                ],
                'timeout' => 60
            ]);

            $data = json_decode($response->getBody(), true);
            
            // Tambahkan informasi periode untuk semua items
            $data['period'] = [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to
            ];
            
            // Tambahkan perhitungan untuk semua items (bulanan)
            foreach ($data['results'] as $itemName => &$itemData) {
                $stockInfo = $this->calculateMonthlyStockDepletion($itemName, $itemData['predictions']);
                $itemData['stock_info'] = $stockInfo;
            }
            
            // Simpan data prediksi ke database
            DB::transaction(function () use ($request, $data) {
                $this->saveAllPredictions($request, $data);
            });
            
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Prediction service unavailable: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cek apakah periode prediksi bertabrakan dengan prediksi sebelumnya
     * Dimodifikasi untuk menangani periode bulanan
     */
private function checkPeriodOverlap($itemName, $dateFrom, $dateTo)
{
    // Konversi ke format bulan untuk perbandingan
    $startMonth = Carbon::parse($dateFrom)->format('Y-m');
    $endMonth = Carbon::parse($dateTo)->format('Y-m');

    $existingPredictions = DB::table('prediksi as p')
        ->join('detail_prediksi as dp', 'p.id_prediksi', '=', 'dp.id_prediksi')
        ->where('dp.nama_item', $itemName)
        ->where(function ($query) use ($startMonth, $endMonth) {
            $query->where(function ($q) use ($startMonth, $endMonth) {
                // Overlap check berdasarkan bulan - GANTI BAGIAN INI
                $q->whereRaw("TO_CHAR(p.tanggal_dari, 'YYYY-MM') <= ?", [$endMonth])
                  ->whereRaw("TO_CHAR(p.tanggal_sampai, 'YYYY-MM') >= ?", [$startMonth]);
            });
        })
        ->select('p.tanggal_dari', 'p.tanggal_sampai', 'p.tanggal', 'dp.nama_item')
        ->get();

    if ($existingPredictions->count() > 0) {
        $details = $existingPredictions->map(function ($prediction) {
            return [
                'item' => $prediction->nama_item,
                'existing_period' => [
                    'from' => Carbon::parse($prediction->tanggal_dari)->format('Y-m'),
                    'to' => Carbon::parse($prediction->tanggal_sampai)->format('Y-m')
                ],
                'prediction_date' => $prediction->tanggal
            ];
        })->toArray();

        return [
            'has_overlap' => true,
            'details' => $details
        ];
    }

    return [
        'has_overlap' => false,
        'details' => []
    ];
}

    private function saveSinglePrediction($request, $data, $stockInfo)
    {
        // Buat record prediksi utama
        $prediksi = Prediksi::create([
            'tanggal' => Carbon::now()->toDateString(),
            'jumlah_item' => 1,
            'tanggal_dari' => $request->date_from,
            'tanggal_sampai' => $request->date_to
        ]);

        // Buat detail prediksi
        $detailPrediksi = DetailPrediksi::create([
            'id_prediksi' => $prediksi->id_prediksi,
            'nama_item' => $request->item_name,
            'stok_tersisa' => $stockInfo['current_stock'],
            'sisa_hari' => $stockInfo['months_until_depletion'], // Ubah ke bulan
            'tanggal_habis' => $stockInfo['depletion_date'] ? Carbon::parse($stockInfo['depletion_date'])->toDateString() : null
        ]);

        // Simpan data prediksi bulanan
        foreach ($data['predictions'] as $prediction) {
            DataPrediksi::create([
                'id_detail_prediksi' => $detailPrediksi->id_detail_prediksi,
                'id_prediksi' => $prediksi->id_prediksi,
                'tanggal' => Carbon::parse($prediction['date'])->toDateString(),
                'jumlah_prediksi' => max(0, round($prediction['predicted_quantity'], 2))
            ]);
        }
    }

    private function saveAllPredictions($request, $data)
    {
        // Buat record prediksi utama
        $prediksi = Prediksi::create([
            'tanggal' => Carbon::now()->toDateString(),
            'jumlah_item' => count($data['results']),
            'tanggal_dari' => $request->date_from,
            'tanggal_sampai' => $request->date_to
        ]);

        // Loop untuk setiap item
        foreach ($data['results'] as $itemName => $itemData) {
            // Buat detail prediksi untuk setiap item
            $detailPrediksi = DetailPrediksi::create([
                'id_prediksi' => $prediksi->id_prediksi,
                'nama_item' => $itemName,
                'stok_tersisa' => $itemData['stock_info']['current_stock'],
                'sisa_hari' => $itemData['stock_info']['months_until_depletion'], // Ubah ke bulan
                'tanggal_habis' => $itemData['stock_info']['depletion_date'] ? 
                    Carbon::parse($itemData['stock_info']['depletion_date'])->toDateString() : null
            ]);

            // Simpan data prediksi bulanan untuk setiap item
            foreach ($itemData['predictions'] as $prediction) {
                DataPrediksi::create([
                    'id_detail_prediksi' => $detailPrediksi->id_detail_prediksi,
                    'id_prediksi' => $prediksi->id_prediksi,
                    'tanggal' => Carbon::parse($prediction['date'])->toDateString(),
                    'jumlah_prediksi' => max(0, round($prediction['predicted_quantity'], 2))
                ]);
            }
        }
    }

    private function calculateMonthlyStockDepletion($itemName, $predictions)
    {
        try {
            // Ambil data barang dari database
            $barang = Barang::where('nama_barang', $itemName)->first();
            
            if (!$barang) {
                return [
                    'current_stock' => 0,
                    'depletion_date' => null,
                    'months_until_depletion' => null,
                    'warning_level' => 'unknown',
                    'message' => 'Data barang tidak ditemukan'
                ];
            }
            
            $currentStock = $barang->jumlah_barang;
            $remainingStock = $currentStock;
            $depletionDate = null;
            $monthsUntilDepletion = null;
            
            // Simulasi pengurangan stok berdasarkan prediksi bulanan
            foreach ($predictions as $index => $prediction) {
                $predictedSales = max(0, round($prediction['predicted_quantity']));
                $remainingStock -= $predictedSales;
                
                // Jika stok habis atau kurang dari 0
                if ($remainingStock <= 0) {
                    $depletionDate = $prediction['date'];
                    $monthsUntilDepletion = $index + 1;
                    break;
                }
            }
            
            // Tentukan level peringatan untuk bulan
            $warningLevel = $this->getMonthlyWarningLevel($monthsUntilDepletion, $currentStock);
            
            return [
                'current_stock' => $currentStock,
                'depletion_date' => $depletionDate,
                'months_until_depletion' => $monthsUntilDepletion,
                'warning_level' => $warningLevel,
                'message' => $this->getMonthlyDepletionMessage($monthsUntilDepletion, $depletionDate)
            ];
            
        } catch (\Exception $e) {
            return [
                'current_stock' => 0,
                'depletion_date' => null,
                'months_until_depletion' => null,
                'warning_level' => 'error',
                'message' => 'Error calculating stock depletion: ' . $e->getMessage()
            ];
        }
    }
    
    private function getMonthlyWarningLevel($monthsUntilDepletion, $currentStock)
    {
        if ($currentStock <= 0) {
            return 'out_of_stock';
        }
        
        if ($monthsUntilDepletion === null) {
            return 'safe';
        }
        
        if ($monthsUntilDepletion <= 1) {
            return 'critical';
        } elseif ($monthsUntilDepletion <= 2) {
            return 'warning';
        } elseif ($monthsUntilDepletion <= 3) {
            return 'caution';
        } else {
            return 'safe';
        }
    }
    
    private function getMonthlyDepletionMessage($monthsUntilDepletion, $depletionDate)
    {
        if ($monthsUntilDepletion === null) {
            return 'Stok aman untuk periode prediksi';
        }
        
        if ($monthsUntilDepletion <= 1) {
            return "⚠️ KRITIS: Stok akan habis bulan depan ($depletionDate)";
        } elseif ($monthsUntilDepletion <= 2) {
            return "🔴 URGENT: Stok akan habis dalam $monthsUntilDepletion bulan ($depletionDate)";
        } elseif ($monthsUntilDepletion <= 3) {
            return "🟡 PERINGATAN: Stok akan habis dalam $monthsUntilDepletion bulan ($depletionDate)";
        } else {
            return "✅ Stok akan habis dalam $monthsUntilDepletion bulan ($depletionDate)";
        }
    }

    public function getHistoricalPredictions(Request $request)
    {
        $prediksi = Prediksi::with(['detailPrediksi.dataPrediksi'])
            ->orderBy('tanggal', 'desc')
            ->take(10)
            ->get();
            
        return response()->json($prediksi);
    }

public function getPredictionById($id)
{
    try {
        // Hapus prefix 'PRD-' jika ada
        $cleanId = str_replace('PRD-', '', strtoupper($id));
        
        // Validasi bahwa $cleanId adalah numeric
        if (!is_numeric($cleanId)) {
            return response()->json(['error' => 'Invalid prediction ID format'], 400);
        }
        
        $prediksi = Prediksi::with(['detailPrediksi.dataPrediksi'])
            ->where('id_prediksi', intval($cleanId))
            ->first();
            
        if (!$prediksi) {
            return response()->json(['error' => 'Prediction not found'], 404);
        }
        
        return response()->json($prediksi);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}

    public function cekPrediksi(Request $request)
{
    $query = Prediksi::query();

    // Filter berdasarkan tanggal prediksi dibuat
    if ($request->filled('tanggal_start')) {
        $query->whereDate('tanggal', '>=', $request->tanggal_start);
    }

    if ($request->filled('tanggal_end')) {
        $query->whereDate('tanggal', '<=', $request->tanggal_end);
    }

    // Filter berdasarkan periode prediksi (ubah untuk bulan)
    if ($request->filled('periode_start')) {
        $query->where('tanggal_dari', '>=', $request->periode_start . '-01');
    }

    if ($request->filled('periode_end')) {
        // Ambil akhir bulan
        $endDate = Carbon::parse($request->periode_end . '-01')->endOfMonth();
        $query->where('tanggal_sampai', '<=', $endDate);
    }

    // Pencarian berdasarkan id_prediksi
    if ($request->filled('search')) {
        $search = str_replace('PRD-', '', strtoupper($request->search));
        if (is_numeric($search)) {
            $query->where('id_prediksi', intval($search));
        }
    }

    $dataPrediksi = $query->orderByDesc('tanggal')->get();

    // Tambahkan format bulan ke response
    $dataPrediksi = $dataPrediksi->map(function ($item) {
        return [
            'id_prediksi' => $item->id_prediksi,
            'tanggal' => $item->tanggal,
            'jumlah_item' => $item->jumlah_item,
            'tanggal_dari' => $item->tanggal_dari,
            'tanggal_sampai' => $item->tanggal_sampai,
            'bulan_dari' => $item->tanggal_dari ? Carbon::parse($item->tanggal_dari)->format('Y-m') : null,
            'bulan_sampai' => $item->tanggal_sampai ? Carbon::parse($item->tanggal_sampai)->format('Y-m') : null,
        ];
    });

    return response()->json($dataPrediksi);
}

    /**
     * Get available dates for a specific item (untuk frontend)
     * Dimodifikasi untuk menangani periode bulanan
     */
    public function getAvailableDates($itemName)
    {
        $usedPeriods = DB::table('prediksi as p')
            ->join('detail_prediksi as dp', 'p.id_prediksi', '=', 'dp.id_prediksi')
            ->where('dp.nama_item', $itemName)
            ->select('p.tanggal_dari', 'p.tanggal_sampai')
            ->orderBy('p.tanggal_dari')
            ->get();

        return response()->json([
            'item_name' => $itemName,
            'used_periods' => $usedPeriods
        ]);
    }
    /**
 * Hapus prediksi berdasarkan ID
 */
public function deletePrediction(Request $request)
{
    $request->validate([
        'id' => 'required'
    ]);
    
    try {
        $id = $request->input('id');
        
        // Hapus prefix 'PRD-' jika ada
        $cleanId = str_replace('PRD-', '', strtoupper($id));
        
        // Validasi bahwa $cleanId adalah numeric
        if (!is_numeric($cleanId)) {
            return response()->json(['error' => 'Invalid prediction ID format'], 400);
        }
        
        $prediksi = Prediksi::find(intval($cleanId));
        
        if (!$prediksi) {
            return response()->json(['error' => 'Prediction not found'], 404);
        }
        
        // Gunakan transaction untuk memastikan semua data terhapus
        DB::transaction(function () use ($prediksi) {
            // Hapus data prediksi terlebih dahulu
            DataPrediksi::where('id_prediksi', $prediksi->id_prediksi)->delete();
            
            // Hapus detail prediksi
            DetailPrediksi::where('id_prediksi', $prediksi->id_prediksi)->delete();
            
            // Hapus prediksi utama
            $prediksi->delete();
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Prediksi berhasil dihapus'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => 'Server error: ' . $e->getMessage()
        ], 500);
    }
}
}