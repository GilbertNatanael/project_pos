@extends('layouts.app')
@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection
@section('content')
<div class="laporan-container">
    <h2 class="laporan-title">Laporan Transaksi</h2>

<div class="laporan-filters">
    <div class="filter-group">
        <label for="start-date">Dari:</label>
        <input type="date" id="start-date">
    </div>
    <div class="filter-group">
        <label for="end-date">Sampai:</label>
        <input type="date" id="end-date">
    </div>
    <div class="filter-group search-group">
        <label for="search-bar">Pencarian:</label>
        <input type="text" id="search-bar" placeholder="Cari transaksi...">
    </div>
    <button class="btn-filter" id="btn-filter">
        <i class="fas fa-filter"></i> Terapkan
    </button>

    <div class="dropdown-export">
        <button class="btn-export-dropdown">
            <i class="fas fa-file-export"></i> Export
        </button>
        <div class="dropdown-menu">
            <a href="#" id="export-pdf"><i class="fas fa-file-pdf"></i> Export PDF</a>
            <a href="#" id="export-excel"><i class="fas fa-file-excel"></i> Export Excel</a>
        </div>
    </div>
</div>
<!-- Filter Metode Pembayaran -->
<select id="metode-pembayaran" class="form-control">
    <option value="">Semua Metode</option>
    <option value="Cash">Cash</option>
    <option value="Card">Card</option>
    <option value="qris">QRIS</option>
</select>

<!-- Filter Range Harga -->
<div style="display: flex; gap: 10px; margin-top: 10px;">
    <input type="number" id="harga-min" placeholder="Harga Min" class="form-control" />
    <input type="number" id="harga-max" placeholder="Harga Max" class="form-control" />
</div>

    <div class="table-wrapper">
    <table class="laporan-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Total Harga</th>
                <th>Metode Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksi as $t)
                <tr>
                    <td>{{ $t->id_transaksi }}</td>
                    <td>{{ \Carbon\Carbon::parse($t->tanggal_waktu)->format('d-m-Y H:i') }}</td>
                    <td>Rp{{ number_format($t->total_harga, 0, ',', '.') }}</td>
                    <td>{{ ucfirst($t->metode_pembayaran) }}</td>
                    <td>
                        <a href="#" class="btn-detail" data-id="{{ $t->id_transaksi }}">
                            <i class="fas fa-eye"></i> Detail
                        </a>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada data transaksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<!-- Modal Detail Transaksi -->
<div id="modal-detail" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <span class="close-button" id="close-modal">&times;</span>
        <h3>Detail Transaksi</h3>
        <div id="detail-body">
            <!-- Data transaksi & detail transaksi akan di-inject dengan JS -->
        </div>
    </div>
</div>

@endsection

@vite(['resources/css/laporan.css', 'resources/js/laporan.js'])
