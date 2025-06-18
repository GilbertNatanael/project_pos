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