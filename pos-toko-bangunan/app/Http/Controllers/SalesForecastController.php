<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Client;
use App\Models\Barang;

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
            
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Prediction service unavailable: ' . $e->getMessage()
            ], 500);
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
}