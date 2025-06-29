@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    <!-- Notifikasi alert -->
    <div id="alert-success" class="alert alert-success" style="display: none;"></div>
    <div id="alert-error" class="alert alert-error" style="display: none;"></div>

    <!-- Header dan Tombol -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Riwayat Pembelian Barang</h1>
        <div class="add-button-container">
            <a href="{{ route('pembelian.tambah') }}" class="add-button" id="btnTambahPembelian">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" class="text-white w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pembelian
            </a>
        </div>
    </div>

<!-- Filter Section -->
<div class="bg-white shadow rounded mb-4 p-4">
    <h3 class="text-lg font-semibold mb-3">Filter Pembelian</h3>
    <form id="filterForm" method="GET" action="{{ route('pembelian') }}">
        <div class="filter-grid">
            <!-- Filter Nama Barang -->
            <div>
                <label for="nama_barang" class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
                <input type="text" 
                       id="nama_barang" 
                       name="nama_barang" 
                       value="{{ request('nama_barang') }}"
                       placeholder="Cari nama barang..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Filter Kategori -->
            <div>
                <label for="kategori_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select id="kategori_id" 
                        name="kategori_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" 
                                {{ request('kategori_id') == $kategori->id ? 'selected' : '' }}>
                            {{ $kategori->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tanggal Dari -->
            <div>
                <label for="tanggal_dari" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Dari</label>
                <input type="date" 
                       id="tanggal_dari" 
                       name="tanggal_dari" 
                       value="{{ request('tanggal_dari') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Filter Tanggal Sampai -->
            <div>
                <label for="tanggal_sampai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Sampai</label>
                <input type="date" 
                       id="tanggal_sampai" 
                       name="tanggal_sampai" 
                       value="{{ request('tanggal_sampai') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Button Group -->
        <div class="filter-buttons mt-4">
            <button type="submit" class="btn-filter">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
                </svg>
                Filter
            </button>
            <button type="button" id="resetFilter" class="btn-reset">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset
            </button>
        </div>
    </form>
</div>

    <!-- Tabel Riwayat Pembelian -->
    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-2">ID</th>
                    <th class="text-left px-4 py-2">Tanggal</th>
                    <th class="text-left px-4 py-2">Total Item</th>
                    <th class="text-left px-4 py-2">Total Harga</th>
                    <th class="text-left px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($pembelian as $item)
                    <tr>
                        <td class="px-4 py-2">{{ $item->id_pembelian }}</td>
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}</td>
                        <td class="px-4 py-2">{{ $item->detailPembelian->sum('jumlah') }}</td>
                        <td class="px-4 py-2">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-2">
                            <button onclick="showDetail({{ $item->id_pembelian }})" class="text-blue-600 hover:underline">Detail</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center px-4 py-2">Belum ada data pembelian.</td></tr>
                @endforelse
            </tbody>

        </table>
    </div>

{{-- Pagination untuk AJAX --}}
@if ($pembelian->hasPages())
    <div class="mt-4 pagination flex justify-center gap-1">
        {{-- Tombol Sebelumnya --}}
        @if ($pembelian->onFirstPage())
            <span class="px-3 py-1 text-gray-400 cursor-not-allowed">«</span>
        @else
            <a href="{{ $pembelian->previousPageUrl() }}" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">«</a>
        @endif

        {{-- Nomor Halaman --}}
        @foreach ($pembelian->getUrlRange(1, $pembelian->lastPage()) as $page => $url)
            @if ($page == $pembelian->currentPage())
                <span class="px-3 py-1 bg-blue-600 text-white rounded">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 page-link">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Tombol Selanjutnya --}}
        @if ($pembelian->hasMorePages())
            <a href="{{ $pembelian->nextPageUrl() }}" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">»</a>
        @else
            <span class="px-3 py-1 text-gray-400 cursor-not-allowed">»</span>
        @endif
    </div>
@endif

</div>

<!-- Modal Overlay - Full screen blocking overlay -->
<div id="modalDetail" class="modal-overlay hidden" style="display: none !important;">
    <div class="modal-backdrop"></div>
    <div class="modal-container">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Detail Pembelian</h2>
                <button id="closeModal" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Merek</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detailBody">
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@vite(['resources/css/pembelian.css', 'resources/js/pembelian.js'])