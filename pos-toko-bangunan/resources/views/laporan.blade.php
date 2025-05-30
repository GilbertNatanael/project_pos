@extends('layouts.app')

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
                        <a href="" class="btn-detail">
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

@endsection

@vite(['resources/css/laporan.css', 'resources/js/laporan.js'])
