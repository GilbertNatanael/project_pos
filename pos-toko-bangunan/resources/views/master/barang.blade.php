@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    @if(session('success'))
        <div id="alert-success" class="alert alert-success">
            {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div id="alert-error" class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Konten tabel barang -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Master Barang</h1>
        <div class="add-button-container">
            <a href="{{ route('create_barang') }}" class="add-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"></path>
                </svg>
                Tambah Barang
            </a>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="flex items-center gap-4 mb-4">
        <form method="GET" action="{{ route('barang') }}" class="flex items-center gap-2 w-full">
            <input type="text" name="search" placeholder="Cari nama atau kode barang..." value="{{ request('search') }}"
                class="border border-gray-300 rounded px-3 py-2 w-full" />
            <select name="filter" class="border border-gray-300 rounded px-3 py-2">
                <option value="">Semua</option>
                <option value="stok_habis" {{ request('filter') == 'stok_habis' ? 'selected' : '' }}>Stok Habis</option>
                <option value="stok_tersedia" {{ request('filter') == 'stok_tersedia' ? 'selected' : '' }}>Stok Tersedia</option>
            </select>
            <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">Cari</button>
        </form>
    </div>

    <!-- Tabel Data Barang -->
    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-2">Kode</th>
                    <th class="text-left px-4 py-2">Nama Barang</th>
                    <th class="text-left px-4 py-2">Harga</th>
                    <th class="text-left px-4 py-2">Jumlah</th>
                    <th class="text-left px-4 py-2">Satuan</th>
                    <th class="text-left px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($barang as $item)
                    <tr data-row="{{ $item->id_barang }}">
                        
                        <td class="px-4 py-2">{{ $item->kode_barang }}</td>
                        <td class="px-4 py-2">{{ $item->nama_barang }}</td>
                        <td class="px-4 py-2">Rp {{ number_format($item->harga_barang, 0, ',', '.') }}</td>
                        <td class="px-4 py-2">{{ $item->jumlah_barang }}</td>
                        <td class="px-4 py-2">{{ $item->satuan_barang ?? '-' }}</td>
                        
                        <td class="px-4 py-2">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <a href="#" 
                                class="edit-button"
                                data-id="{{ $item->id_barang }}"
                                data-kode="{{ $item->kode_barang }}"
                                data-nama="{{ $item->nama_barang }}"
                                data-harga="{{ $item->harga_barang }}"
                                data-jumlah="{{ $item->jumlah_barang }}"
                                data-satuan="{{ $item->satuan_barang }}">
                                Edit
                             </a>
                             
                                <form action="{{ route('barang.destroy', $item->id_barang) }}" 
                                      method="POST" onsubmit="return confirm('Yakin hapus barang ini?')"
                                      style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-button">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">Tidak ada data barang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $barang->links() }}
    </div>
</div>

@endsection

@vite(['resources/css/barang.css', 'resources/js/barang.js'])