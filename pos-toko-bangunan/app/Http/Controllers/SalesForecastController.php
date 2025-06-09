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
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);
            $daysAhead = $dateFrom->diffInDays($dateTo) + 1;

            $client = new Client();
            $response = $client->post($this->flaskApiUrl . '/predict', [
                'json' => [
                    'item_name' => $request->item_name,
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                    'days_ahead' => $daysAhead
                ],
                'timeout' => 30
            ]);

            $data = json_decode($response->getBody(), true);
            
            // Tambahkan informasi periode
            $data['date_from'] = $request->date_from;
            $data['date_to'] = $request->date_to;
            
            // Tambahkan perhitungan kapan barang habis
            $stockInfo = $this->calculateStockDepletion($request->item_name, $data['predictions']);
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
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);
            $daysAhead = $dateFrom->diffInDays($dateTo) + 1;

            $client = new Client();
            $response = $client->post($this->flaskApiUrl . '/predict/all', [
                'json' => [
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                    'days_ahead' => $daysAhead
                ],
                'timeout' => 60
            ]);

            $data = json_decode($response->getBody(), true);
            
            // Tambahkan informasi periode untuk semua items
            $data['period'] = [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to
            ];
            
            // Tambahkan perhitungan untuk semua items
            foreach ($data as $itemName => &$itemData) {
                if ($itemName === 'period') continue;
                
                $stockInfo = $this->calculateStockDepletion($itemName, $itemData['predictions']);
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
     */
    private function checkPeriodOverlap($itemName, $dateFrom, $dateTo)
    {
        $existingPredictions = DB::table('prediksi as p')
            ->join('detail_prediksi as dp', 'p.id_prediksi', '=', 'dp.id_prediksi')
            ->where('dp.nama_item', $itemName)
            ->where(function ($query) use ($dateFrom, $dateTo) {
                // Cek apakah ada overlap periode
                $query->where(function ($q) use ($dateFrom, $dateTo) {
                    // Case 1: New period starts within existing period
                    $q->where('p.tanggal_dari', '<=', $dateFrom)
                      ->where('p.tanggal_sampai', '>=', $dateFrom);
                })->orWhere(function ($q) use ($dateFrom, $dateTo) {
                    // Case 2: New period ends within existing period
                    $q->where('p.tanggal_dari', '<=', $dateTo)
                      ->where('p.tanggal_sampai', '>=', $dateTo);
                })->orWhere(function ($q) use ($dateFrom, $dateTo) {
                    // Case 3: New period encompasses existing period
                    $q->where('p.tanggal_dari', '>=', $dateFrom)
                      ->where('p.tanggal_sampai', '<=', $dateTo);
                })->orWhere(function ($q) use ($dateFrom, $dateTo) {
                    // Case 4: Existing period encompasses new period
                    $q->where('p.tanggal_dari', '<=', $dateFrom)
                      ->where('p.tanggal_sampai', '>=', $dateTo);
                });
            })
            ->select('p.tanggal_dari', 'p.tanggal_sampai', 'p.tanggal', 'dp.nama_item')
            ->get();

        if ($existingPredictions->count() > 0) {
            $details = $existingPredictions->map(function ($prediction) {
                return [
                    'item' => $prediction->nama_item,
                    'existing_period' => [
                        'from' => $prediction->tanggal_dari,
                        'to' => $prediction->tanggal_sampai
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

    /**
     * Get available dates for a specific item (untuk frontend)
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
            'sisa_hari' => $stockInfo['days_until_depletion'],
            'tanggal_habis' => $stockInfo['depletion_date'] ? Carbon::parse($stockInfo['depletion_date'])->toDateString() : null
        ]);

        // Simpan data prediksi harian
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
            'jumlah_item' => count($data) - 1, // -1 karena ada key 'period'
            'tanggal_dari' => $request->date_from,
            'tanggal_sampai' => $request->date_to
        ]);

        // Loop untuk setiap item
        foreach ($data as $itemName => $itemData) {
            if ($itemName === 'period') continue; // Skip period info
            
            // Buat detail prediksi untuk setiap item
            $detailPrediksi = DetailPrediksi::create([
                'id_prediksi' => $prediksi->id_prediksi,
                'nama_item' => $itemName,
                'stok_tersisa' => $itemData['stock_info']['current_stock'],
                'sisa_hari' => $itemData['stock_info']['days_until_depletion'],
                'tanggal_habis' => $itemData['stock_info']['depletion_date'] ? 
                    Carbon::parse($itemData['stock_info']['depletion_date'])->toDateString() : null
            ]);

            // Simpan data prediksi harian untuk setiap item
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

    private function calculateStockDepletion($itemName, $predictions)
    {
        try {
            // Ambil data barang dari database
            $barang = Barang::where('nama_barang', $itemName)->first();
            
            if (!$barang) {
                return [
                    'current_stock' => 0,
                    'depletion_date' => null,
                    'days_until_depletion' => null,
                    'warning_level' => 'unknown',
                    'message' => 'Data barang tidak ditemukan'
                ];
            }
            
            $currentStock = $barang->jumlah_barang;
            $remainingStock = $currentStock;
            $depletionDate = null;
            $daysUntilDepletion = null;
            
            // Simulasi pengurangan stok berdasarkan prediksi
            foreach ($predictions as $index => $prediction) {
                $predictedSales = max(0, round($prediction['predicted_quantity']));
                $remainingStock -= $predictedSales;
                
                // Jika stok habis atau kurang dari 0
                if ($remainingStock <= 0) {
                    $depletionDate = $prediction['date'];
                    $daysUntilDepletion = $index + 1;
                    break;
                }
            }
            
            // Tentukan level peringatan
            $warningLevel = $this->getWarningLevel($daysUntilDepletion, $currentStock);
            
            return [
                'current_stock' => $currentStock,
                'depletion_date' => $depletionDate,
                'days_until_depletion' => $daysUntilDepletion,
                'warning_level' => $warningLevel,
                'message' => $this->getDepletionMessage($daysUntilDepletion, $depletionDate)
            ];
            
        } catch (\Exception $e) {
            return [
                'current_stock' => 0,
                'depletion_date' => null,
                'days_until_depletion' => null,
                'warning_level' => 'error',
                'message' => 'Error calculating stock depletion: ' . $e->getMessage()
            ];
        }
    }
    
    private function getWarningLevel($daysUntilDepletion, $currentStock)
    {
        if ($currentStock <= 0) {
            return 'out_of_stock';
        }
        
        if ($daysUntilDepletion === null) {
            return 'safe';
        }
        
        if ($daysUntilDepletion <= 3) {
            return 'critical';
        } elseif ($daysUntilDepletion <= 7) {
            return 'warning';
        } elseif ($daysUntilDepletion <= 14) {
            return 'caution';
        } else {
            return 'safe';
        }
    }
    
    private function getDepletionMessage($daysUntilDepletion, $depletionDate)
    {
        if ($daysUntilDepletion === null) {
            return 'Stok aman untuk periode prediksi';
        }
        
        if ($daysUntilDepletion <= 1) {
            return "⚠️ KRITIS: Stok akan habis besok ($depletionDate)";
        } elseif ($daysUntilDepletion <= 3) {
            return "🔴 URGENT: Stok akan habis dalam $daysUntilDepletion hari ($depletionDate)";
        } elseif ($daysUntilDepletion <= 7) {
            return "🟡 PERINGATAN: Stok akan habis dalam $daysUntilDepletion hari ($depletionDate)";
        } elseif ($daysUntilDepletion <= 14) {
            return "🟠 PERHATIAN: Stok akan habis dalam $daysUntilDepletion hari ($depletionDate)";
        } else {
            return "✅ Stok akan habis dalam $daysUntilDepletion hari ($depletionDate)";
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
        $prediksi = Prediksi::with(['detailPrediksi.dataPrediksi'])
            ->where('id_prediksi', $id)
            ->first();
            
        if (!$prediksi) {
            return response()->json(['error' => 'Prediction not found'], 404);
        }
        
        return response()->json($prediksi);
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

    // Filter berdasarkan periode prediksi
    if ($request->filled('periode_start')) {
        $query->whereDate('tanggal_dari', '>=', $request->periode_start);
    }

    if ($request->filled('periode_end')) {
        $query->whereDate('tanggal_sampai', '<=', $request->periode_end);
    }

    // Pencarian berdasarkan id_prediksi
    if ($request->filled('search')) {
        $search = str_replace('PRD-', '', strtoupper($request->search));
        if (is_numeric($search)) {
            $query->where('id_prediksi', intval($search));
        }
    }

    $dataPrediksi = $query->orderByDesc('tanggal')->get();

    return response()->json($dataPrediksi);
}




}