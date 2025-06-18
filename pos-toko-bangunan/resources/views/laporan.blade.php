@extends('layouts.app')
@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection
@section('content')
<div class="laporan-container">
    <h2 class="laporan-title">Laporan Transaksi</h2>

<div class="d-flex flex-wrap gap-2 mb-3 align-items-end">
    <!-- Filter: Tanggal -->
    <div class="form-group">
        <label for="start-date">Dari Tanggal</label>
        <input type="date" id="start-date" name="start_date" class="form-control"
            value="{{ request('start_date') }}">
    </div>
    <div class="form-group">
        <label for="end-date">Sampai Tanggal</label>
        <input type="date" id="end-date" name="end_date" class="form-control"
            value="{{ request('end_date') }}">
    </div>

    <!-- Filter: Keyword -->
    <div class="form-group flex-grow-1">
        <label for="search-bar">Cari Transaksi</label>
        <input type="text" id="search-bar" name="keyword" class="form-control"
            value="{{ request('keyword') }}" placeholder="ID atau catatan...">
    </div>

    <!-- Tombol Filter Tambahan -->
    <div>
        <button class="btn btn-outline-primary" type="button" id="toggleFilter">
            <i class="bi bi-funnel-fill"></i> Filter Lanjutan
        </button>
    </div>

    <!-- Tombol Terapkan -->
    <div>
        <button class="btn btn-success" id="filter-button" type="button">
            <i class="bi bi-check-circle-fill"></i> Terapkan
        </button>
    </div>

    <!-- Tambah tombol Reset -->
<div>
    <button class="btn btn-secondary" id="reset-button" type="button">
        <i class="bi bi-arrow-clockwise"></i> Reset Filter
    </button>
</div>

</div>

<!-- Filter Tambahan: Hidden by Default -->
<div id="advanced-filters" class="border rounded p-3 mb-3 bg-light" style="display: none;">
    <div class="row">
        <!-- Metode Pembayaran -->
        <div class="col-md-4 mb-2">
            <label for="metode-pembayaran">Metode Pembayaran</label>
            <select id="metode-pembayaran" class="form-select">
                <option value="" {{ request('metode') == '' ? 'selected' : '' }}>Semua</option>
                <option value="cash" {{ request('metode') == 'Cash' ? 'selected' : '' }}>Cash</option>
                <option value="card" {{ request('metode') == 'Card' ? 'selected' : '' }}>Card</option>
                <option value="qris" {{ request('metode') == 'qris' ? 'selected' : '' }}>QRIS</option>
            </select>
        </div>

        <!-- Range Harga -->
        <div class="col-md-4 mb-2">
            <label for="harga-min">Harga Minimum</label>
            <input type="number" id="harga-min" class="form-control" value="{{ request('harga_min') }}" placeholder="Contoh: 10000">
        </div>

        <div class="col-md-4 mb-2">
            <label for="harga-max">Harga Maksimum</label>
            <input type="number" id="harga-max" class="form-control" value="{{ request('harga_max') }}" placeholder="Contoh: 1000000">
        </div>
    </div>
</div>
<div class="d-flex gap-2 mb-3">
    <select id="export-format" class="form-select w-auto">
        <option value="excel">Export Excel</option>
        <option value="pdf">Export PDF</option>
    </select>
    <button class="btn btn-outline-dark" id="export-button" type="button">
        <i class="bi bi-download"></i> Export
    </button>
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
<!-- Pagination -->
@if ($transaksi->hasPages())
    <div class="pagination-container">
        <div class="pagination">
            {{-- Tombol Sebelumnya --}}
            @if ($transaksi->onFirstPage())
                <span>«</span>
            @else
                <a href="{{ $transaksi->previousPageUrl() }}">«</a>
            @endif

            {{-- Nomor Halaman --}}
            @foreach ($transaksi->getUrlRange(1, $transaksi->lastPage()) as $page => $url)
                @if ($page == $transaksi->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Tombol Selanjutnya --}}
            @if ($transaksi->hasMorePages())
                <a href="{{ $transaksi->nextPageUrl() }}">»</a>
            @else
                <span>»</span>
            @endif
        </div>
        
    </div>
@endif
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
