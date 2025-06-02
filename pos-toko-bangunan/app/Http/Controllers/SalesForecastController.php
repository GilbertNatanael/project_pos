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
            'days_ahead' => 'integer|min:1|max:365'
        ]);

        try {
            $client = new Client();
            $response = $client->post($this->flaskApiUrl . '/predict', [
                'json' => [
                    'item_name' => $request->item_name,
                    'days_ahead' => $request->days_ahead ?? 7
                ],
                'timeout' => 30
            ]);

            $data = json_decode($response->getBody(), true);
            
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
            'days_ahead' => 'integer|min:1|max:365'
        ]);

        try {
            $client = new Client();
            $response = $client->post($this->flaskApiUrl . '/predict/all', [
                'json' => [
                    'days_ahead' => $request->days_ahead ?? 7
                ],
                'timeout' => 60
            ]);

            $data = json_decode($response->getBody(), true);
            
            // Tambahkan perhitungan untuk semua items
            foreach ($data as $itemName => &$itemData) {
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
     * Simpan prediksi single item ke database
     */
    private function saveSinglePrediction($request, $data, $stockInfo)
    {
        // Buat record prediksi utama
        $prediksi = Prediksi::create([
            'tanggal' => Carbon::now()->toDateString(),
            'jumlah_item' => 1,
            'jumlah_hari' => $request->days_ahead ?? 7
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

    /**
     * Simpan prediksi semua item ke database
     */
    private function saveAllPredictions($request, $data)
    {
        // Buat record prediksi utama
        $prediksi = Prediksi::create([
            'tanggal' => Carbon::now()->toDateString(),
            'jumlah_item' => count($data),
            'jumlah_hari' => $request->days_ahead ?? 7
        ]);

        // Loop untuk setiap item
        foreach ($data as $itemName => $itemData) {
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
    
    /**
     * Hitung kapan barang akan habis berdasarkan stok dan prediksi penjualan
     */
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
    
    /**
     * Tentukan level peringatan berdasarkan hari hingga habis
     */
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
    
    /**
     * Generate pesan berdasarkan prediksi habis stok
     */
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

    /**
     * Get historical predictions - method tambahan untuk melihat riwayat prediksi
     */
    public function getHistoricalPredictions(Request $request)
    {
        $prediksi = Prediksi::with(['detailPrediksi.dataPrediksi'])
            ->orderBy('tanggal', 'desc')
            ->take(10)
            ->get();
            
        return response()->json($prediksi);
    }

    /**
     * Get prediction by ID - method tambahan untuk melihat detail prediksi
     */
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
}