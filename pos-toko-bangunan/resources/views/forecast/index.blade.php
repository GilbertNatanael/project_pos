@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Additional isolation for Bootstrap within forecast content */
        .forecast-content {
            /* Reset any inherited sidebar styles */
            all: revert;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        /* Ensure Bootstrap works properly in forecast content */
        .forecast-content * {
            box-sizing: border-box;
        }
    </style>
@endsection

@section('content')
<div class="forecast-content">
    <div class="container-fluid forecast-container">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">📊 Sales Forecasting Dashboard</h1>
                
                <!-- Control Panel -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Forecast Controls</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="item-select" class="form-label">Select Item:</label>
                                <select id="item-select" class="form-select">
                                    @foreach($items as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="days-input" class="form-label">Days Ahead:</label>
                                <input type="number" id="days-input" class="form-control" value="7" min="1" max="365">
                            </div>
                            <div class="col-md-5 d-flex align-items-end">
                                <button onclick="predictSingle()" class="btn btn-primary me-2">
                                    🔍 Predict Single Item
                                </button>
                                <button onclick="predictAll()" class="btn btn-success">
                                    📈 Predict All Items
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="loading" class="loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Generating forecast...</p>
                </div>

                <!-- Results Section -->
                <div id="results"></div>
                
                <!-- Chart Section -->
                <div id="chart-section" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5>📊 Forecast Visualization</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="forecast-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@vite(['resources/css/forecast.css', 'resources/js/forecast.js'])