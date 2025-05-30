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

            </tbody>
        </table>
    </div>

    <!-- Placeholder Pagination -->
    <div class="mt-4 pagination">
        <a href="#">«</a>
        <a href="#" class="active">1</a>
        <a href="#">2</a>
        <a href="#">»</a>
    </div>
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
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detailBody">
                        <!-- Data detail akan di-inject JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@vite(['resources/css/pembelian.css', 'resources/js/pembelian.js'])