@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <h1>Dashboard</h1>

    <div class="stats">
        <div class="card">
            <h3>Total Income</h3>
            <p>Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="chart-container">
        <canvas id="salesChart"></canvas>
    </div>

    <div class="card-section">
        <div class="card">
            <h3>Barang Hampir Habis</h3>
            <ul>
                @foreach ($barangHampirHabis as $barang)
                    <li>{{ $barang->nama_barang }} ({{ $barang->jumlah_barang }})</li>
                @endforeach
            </ul>
        </div>

        <div class="card">
            <h3>Barang Terpopuler</h3>
            <ul>
                @foreach ($barangTerpopuler as $item)
                    <li>{{ $item->barang->nama_barang }} ({{ $item->total_terjual }} terjual)</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<!-- Modal Info Barang -->
<div id="infoModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Info Barang</h2>
        <p>Detail info barang akan ditampilkan di sini.</p>
    </div>
</div>
@endsection
<script>
    window.salesData = @json($penjualanBulanan->pluck('total'));
    window.salesLabels = @json($penjualanBulanan->pluck('bulan'));
</script>


@vite(['resources/css/dashboard.css', 'resources/js/dashboard.js'])
