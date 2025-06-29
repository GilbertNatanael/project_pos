@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="detail-prediksi-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="detail-title">Detail Prediksi {{ 'PRD-' . str_pad($prediksi->id_prediksi, 4, '0', STR_PAD_LEFT) }}</h2>
        <a href="{{ route('cek_prediksi') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Info Prediksi --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Prediksi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Kode Prediksi:</strong></td>
                            <td>{{ 'PRD-' . str_pad($prediksi->id_prediksi, 4, '0', STR_PAD_LEFT) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Prediksi:</strong></td>
                            <td>{{ $prediksi->tanggal->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Jumlah Item:</strong></td>
                            <td>{{ $prediksi->jumlah_item }} item</td>
                        </tr>
                        <tr>
                            <td><strong>Periode Prediksi:</strong></td>
                            <td>{{ $prediksi->tanggal_dari->format('F Y') }} s.d {{ $prediksi->tanggal_sampai->format('F Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Ringkasan Data</h5>
                </div>
                <div class="card-body">
                    <div id="accuracy-summary">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Menghitung ringkasan...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    {{-- Detail Item Prediksi --}}
<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Detail Item Prediksi</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Item</th>
                    <th>Stok Tersisa</th>
                    <th>Reorder Point</th>
                    <th>Order Quantity</th>
                    <th>Prediksi Habis</th>
                    <th>Status</th>
                    <th>Status Reorder</th>
                </tr>
            </thead>
                <tbody>
                @foreach($detailPrediksi as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->nama_item }}</td>
                    <td>{{ $detail->stok_tersisa_formatted ?? $detail->stok_tersisa }} {{ $detail->satuan_barang ?? 'unit' }}</td>
                    <td>{{ $detail->reorder_point }} {{ $detail->satuan_barang ?? 'unit' }}</td>
                    <td>{{ $detail->order_quantity }} {{ $detail->satuan_barang ?? 'unit' }}</td>
                    <td>
                        @if($detail->tanggal_habis)
                            {{ $detail->tanggal_habis->format('F Y') }}
                        @else
                            <span class="text-muted">Tidak habis dalam periode</span>
                        @endif
                    </td>
                    <td>
                        @if($detail->tanggal_habis)
                            @if($detail->tanggal_habis->isPast())
                                <span class="badge bg-danger">Sudah Lewat</span>
                            @elseif($detail->tanggal_habis->format('Y-m') == now()->format('Y-m'))
                                <span class="badge bg-warning">Bulan Ini</span>
                            @else
                                <span class="badge bg-success">Akan Datang</span>
                            @endif
                        @else
                            <span class="badge bg-info">Stok Aman</span>
                        @endif
                    </td>
                    <td>
                        @if($detail->reorder_status == 'perlu_pesan')
                            <span class="badge bg-danger">Perlu Pesan</span>
                        @else
                            <span class="badge bg-success">Stok Aman</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    </div>
</div>

    {{-- Grafik Perbandingan --}}
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Grafik Perbandingan Data Prediksi vs Aktual (Bulanan)</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Keterangan:</strong> 
                        Grafik ini menunjukkan perbandingan antara prediksi konsumsi bulanan dengan data aktual transaksi per bulan. 
                        Data aktual dihitung dari total transaksi dalam bulan yang sama. Analisis fokus pada selisih antara prediksi dan aktual.
                    </div>
                </div>
            </div>
            
            @foreach($chartData as $index => $item)
            <div class="chart-container mb-4">
                <h6 class="mb-3">{{ $item['nama_item'] }}</h6>
                <div class="row">
                    <div class="col-md-8">
                        <canvas id="chart-{{ $index }}" height="400"></canvas>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Detail Data</h6>
                            </div>
                            <div class="card-body">
                                <small>
                                    <strong>Stok Tersisa:</strong> {{ $item['stok_tersisa'] }} {{ $item['satuan_barang'] ?? 'unit' }}<br>
                                    <strong>Prediksi Habis:</strong> 
                                    @if($item['tanggal_habis'])
                                        {{ \Carbon\Carbon::parse($item['tanggal_habis'])->format('F Y') }}
                                    @else
                                        <span class="text-muted">Tidak habis</span>
                                    @endif
                                </small>
                                <hr>
                                <div id="data-table-{{ $index }}">
                                    <div class="text-center">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                        <div class="mt-1"><small>Loading data...</small></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            @endforeach
        </div>
    </div>
</div>

{{-- Pass data to JavaScript --}}
<script>
    // Make chart data available to the external JavaScript file
    window.chartDataFromServer = @json($chartData);
</script>
@endsection

@vite(['resources/css/detail_prediksi.css','resources/js/detail_prediksi.js'])