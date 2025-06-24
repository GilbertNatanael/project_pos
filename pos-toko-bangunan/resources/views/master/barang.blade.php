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

<!-- Search & Filter - Bagian yang diubah -->
    <div class="flex items-center gap-4 mb-4">
        <form method="GET" action="{{ route('barang') }}" class="flex items-center gap-2 w-full">
            <input type="text" name="search" placeholder="Cari nama atau kode barang..." value="{{ request('search') }}"
                class="border border-gray-300 rounded px-3 py-2 w-full" />
            
            <!-- Filter Kategori -->
            <select name="kategori" class="border border-gray-300 rounded px-3 py-2">
                <option value="">Semua Kategori</option>
                @foreach($kategori as $k)
                    <option value="{{ $k->id }}" {{ request('kategori') == $k->id ? 'selected' : '' }}>
                        {{ $k->nama_kategori }}
                    </option>
                @endforeach
            </select>
            
            <!-- Filter Stok -->
            <select name="filter" class="border border-gray-300 rounded px-3 py-2">
                <option value="">Semua Stok</option>
                <option value="stok_habis" {{ request('filter') == 'stok_habis' ? 'selected' : '' }}>Stok Habis</option>
                <option value="stok_tersedia" {{ request('filter') == 'stok_tersedia' ? 'selected' : '' }}>Stok Tersedia</option>
            </select>
            
            <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">Cari</button>
        </form>
    </div>

    <!-- Tabel Data Barang -->
    <!-- Tabel Data Barang - Bagian yang perlu diganti -->
<div class="bg-white shadow rounded overflow-x-auto">
    <table class="w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="text-left px-4 py-2">Kode</th>
                <th class="text-left px-4 py-2">Nama</th>
                <th class="text-left px-4 py-2">Merk</th>
                <th class="text-left px-4 py-2">Kategori</th>
                <th class="text-left px-4 py-2">Satuan</th>
                <th class="text-left px-4 py-2">Jumlah</th>
                <th class="text-left px-4 py-2">Harga</th>
                <th class="text-left px-4 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($barang as $item)
                <tr data-row="{{ $item->id_barang }}">
                    <td class="px-4 py-2">{{ $item->kode_barang }}</td>
                    <td class="px-4 py-2">{{ $item->nama_barang }}</td>
                    <td class="px-4 py-2">{{ $item->merek ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $item->kategori->nama_kategori ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $item->satuan_barang ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $item->jumlah_barang }}</td>
                    <td class="px-4 py-2">Rp {{ number_format($item->harga_barang, 0, ',', '.') }}</td>
                    <td class="px-4 py-2">
                        <!-- Ganti bagian edit button di tabel (sekitar baris 60-70) -->
                        <a href="#" class="edit-button"
                        data-id="{{ $item->id_barang }}"
                        data-kode="{{ $item->kode_barang }}"
                        data-nama="{{ $item->nama_barang }}"
                        data-harga="{{ $item->harga_barang }}"
                        data-jumlah="{{ $item->jumlah_barang }}"
                        data-satuan="{{ $item->satuan_barang }}"
                        data-merk="{{ $item->merek }}"
                        data-kategori_id="{{ $item->kategori_id }}">
                        Edit
                        </a>

                        <!-- Tambahkan script untuk menyediakan data kategori ke JavaScript -->
                        <script>
                        window.kategoriList = @json($kategori ?? []);
                        </script>
                        <form action="{{ route('barang.destroy', $item->id_barang) }}" method="POST" class="inline-button" onsubmit="return confirm('Yakin hapus barang ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-button">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center px-4 py-4 text-gray-500">Tidak ada data barang.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($barang->hasPages())
    <div class="mt-4 pagination flex justify-center gap-1">
        {{-- Tombol Sebelumnya --}}
        @if ($barang->onFirstPage())
            <span class="px-3 py-1 text-gray-400 cursor-not-allowed">«</span>
        @else
            <a href="{{ $barang->previousPageUrl() }}" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">«</a>
        @endif

        {{-- Nomor Halaman --}}
        @foreach ($barang->getUrlRange(1, $barang->lastPage()) as $page => $url)
            @if ($page == $barang->currentPage())
                <span class="px-3 py-1 bg-blue-600 text-white rounded">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Tombol Selanjutnya --}}
        @if ($barang->hasMorePages())
            <a href="{{ $barang->nextPageUrl() }}" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">»</a>
        @else
            <span class="px-3 py-1 text-gray-400 cursor-not-allowed">»</span>
        @endif
    </div>
@endif

</div>

@endsection

@vite(['resources/css/barang.css', 'resources/js/barang.js'])