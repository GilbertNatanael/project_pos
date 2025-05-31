<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Client;

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
                'timeout' => 30 // Add timeout to prevent hanging
            ]);

            $data = json_decode($response->getBody(), true);
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
                'timeout' => 60 // Longer timeout for all items
            ]);

            $data = json_decode($response->getBody(), true);
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Prediction service unavailable: ' . $e->getMessage()
            ], 500);
        }
    }
}
