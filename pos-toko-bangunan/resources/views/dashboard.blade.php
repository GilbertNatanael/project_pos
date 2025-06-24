@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <h1>Dashboard</h1>
    <div class="welcome-banner">
        <h2>Selamat Datang di Dashboard</h2>
        <p>Statistik toko bangunan X untuk {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
    </div>

    <div class="stats-row">
        <div class="card mini-card">
            <h4>Total Income</h4>
            <p>Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
        </div>
        <div class="card mini-card">
            <h4>Transaksi Bulan Ini</h4>
            <p>{{ $totalTransaksiBulanIni }} transaksi</p>
        </div>
        <div class="card mini-card">
            <h4>Barang Terjual</h4>
            <p>{{ $jumlahBarangTerjual }} pcs</p>
        </div>
        <div class="card mini-card">
            <h4>Produk Aktif</h4>
            <p>{{ $jumlahProdukAktif }} barang</p>
        </div>
    </div>
    <div class="chart-container">
        <canvas id="salesChart"></canvas>
    </div>

    <div class="card-section">
        <!-- Hapus bagian Barang Hampir Habis -->

        <div class="card">
    <h3>5 Barang Terpopuler Bulan Ini</h3>
    <ol class="popular-list">
        @forelse ($barangTerpopuler as $index => $item)
            <li>
                <strong>#{{ $index + 1 }} {{ $item->barang->nama_barang }}</strong><br>
                <span class="terjual-info">{{ $item->total_terjual }} terjual</span>
            </li>
        @empty
            <li><em>Tidak ada penjualan bulan ini.</em></li>
        @endforelse
    </ol>
</div>

    </div>
</div>
@endsection

<script>
    window.salesData = @json($penjualanBulanan->pluck('total'));
    window.salesLabels = @json($penjualanBulanan->pluck('bulan'));
</script>

@vite(['resources/css/dashboard.css', 'resources/js/dashboard.js'])
