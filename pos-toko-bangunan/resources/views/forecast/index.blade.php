@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .forecast-content {
            all: revert;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .forecast-content * {
            box-sizing: border-box;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
        
        .stock-warning {
            border-left: 4px solid;
        }
        
        .stock-warning.critical {
            border-color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }
        
        .stock-warning.warning {
            border-color: #ffc107;
            background-color: rgba(255, 193, 7, 0.1);
        }
        
        .stock-warning.caution {
            border-color: #17a2b8;
            background-color: rgba(23, 162, 184, 0.1);
        }
        
        .stock-warning.safe {
            border-color: #28a745;
            background-color: rgba(40, 167, 69, 0.1);
        }
        
        .period-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .item-card {
            transition: transform 0.2s;
        }
        
        .item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
@endsection

@section('content')
<div class="forecast-content">
    <div class="container-fluid forecast-container">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">📊 Monthly Sales Forecasting Dashboard</h1>
                
                <!-- Control Panel -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">🎯 Forecast Controls - Monthly Predictions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="item-select" class="form-label fw-bold">Select Item:</label>
                                <select id="item-select" class="form-select">
                                    @foreach($items as $item)
                                        <option value="{{ $item }}">{{ $item }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date-from" class="form-label fw-bold">From Month:</label>
                                <input type="month" id="date-from" class="form-control" value="{{ date('Y-m') }}">
                                <small class="text-muted">Starting month for prediction</small>
                            </div>
                            <div class="col-md-3">
                                <label for="date-to" class="form-label fw-bold">To Month:</label>
                                <input type="month" id="date-to" class="form-control" value="{{ date('Y-m', strtotime('+6 months')) }}">
                                <small class="text-muted">Ending month for prediction</small>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="d-grid gap-2 w-100">
                                    <button onclick="predictSingle()" class="btn btn-primary">
                                        🔍 Predict Single Item
                                    </button>
                                    <button onclick="predictAll()" class="btn btn-success">
                                        📈 Predict All Items
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Period Info Display -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <strong>ℹ️ Note:</strong> This system provides monthly forecasts. Select start and end months to predict sales quantities for each month in the specified period.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="loading" class="loading">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 fs-5">🔄 Generating monthly forecast...</p>
                </div>

                <!-- Results Section -->
                <div id="results"></div>
                
                <!-- Chart Section -->
                <div id="chart-section" style="display: none;">
                    <div class="card item-card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">📊 Monthly Forecast Visualization</h5>
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

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // CSRF Token setup
    window.csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
</script>
@vite(['resources/css/forecast.css', 'resources/js/forecast.js'])
@endsection