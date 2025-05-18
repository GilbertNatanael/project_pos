@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Notifikasi alert muncul di sini -->
    @if(session('success'))
        <div id="alert-success" class="alert alert-success">
            {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div id="alert-error" class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Konten Tabel Prediksi -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Prediksi Penjualan</h1>
    </div>

    <!-- Tabel Prediksi Barang -->
    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-2">Kode Barang</th>
                    <th class="text-left px-4 py-2">Nama Barang</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <!-- Barang 1 -->
                <tr>
                    <td class="px-4 py-2">B001</td>
                    <td class="px-4 py-2">Semen</td>
                </tr>
                <!-- Barang 2 -->
                <tr>
                    <td class="px-4 py-2">B002</td>
                    <td class="px-4 py-2">Pasir</td>
                </tr>
                <!-- Barang 3 -->
                <tr>
                    <td class="px-4 py-2">B003</td>
                    <td class="px-4 py-2">Batu Split</td>
                </tr>
                <!-- Barang 4 -->
                <tr>
                    <td class="px-4 py-2">B004</td>
                    <td class="px-4 py-2">Cat Tembok</td>
                </tr>
                <!-- Barang 5 -->
                <tr>
                    <td class="px-4 py-2">B005</td>
                    <td class="px-4 py-2">Paku</td>
                </tr>
                <!-- Barang 6 -->
                <tr>
                    <td class="px-4 py-2">B006</td>
                    <td class="px-4 py-2">Kabel Listrik</td>
                </tr>
                <!-- Barang 7 -->
                <tr>
                    <td class="px-4 py-2">B007</td>
                    <td class="px-4 py-2">Pintu</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Tombol Mulai Prediksi -->
    <div class="mt-4 flex justify-end">
        <button id="start-prediction" class="bg-blue-500 text-white px-6 py-2 rounded">
            Mulai Prediksi
        </button>
    </div>

    <!-- Modal Pilihan Bulan untuk Prediksi -->
    <div id="modalPrediksi" class="modal">
        <div class="modal-content">
            <h2 class="text-lg font-bold mb-4">Pilih Jumlah Bulan</h2>
            <select id="jumlahBulan" class="border p-2 rounded mb-4 w-full">
                @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">{{ $i }} Bulan</option>
                @endfor
            </select>
            <div class="flex justify-end gap-4">
                <button id="cancelPrediksi" class="bg-red-500 text-white px-4 py-2 rounded">Batal</button>
                <button id="confirmPrediksi" class="bg-green-500 text-white px-4 py-2 rounded">Mulai Prediksi</button>
            </div>
        </div>
    </div>
</div>
@endsection

@vite(['resources/css/prediksi.css', 'resources/js/prediksi.js'])
