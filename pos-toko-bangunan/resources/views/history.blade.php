@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Riwayat Aktivitas</h2>
    
    <!-- Filter Form -->
    <div class="filter-section">
        <form method="GET" action="{{ route('history') }}" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="nama_barang">Nama Barang:</label>
                    <input type="text" 
                           id="nama_barang" 
                           name="nama_barang" 
                           value="{{ request('nama_barang') }}" 
                           placeholder="Cari nama barang..."
                           class="form-control">
                </div>
                
                <div class="filter-group">
                    <label for="nama_karyawan">Nama Karyawan:</label>
                    <input type="text" 
                           id="nama_karyawan" 
                           name="nama_karyawan" 
                           value="{{ request('nama_karyawan') }}" 
                           placeholder="Cari nama karyawan..."
                           class="form-control">
                </div>
                
                <div class="filter-group">
                    <label for="aksi">Aksi:</label>
                    <select id="aksi" name="aksi" class="form-control">
                        <option value="">Semua Aksi</option>
                        <option value="tambah" {{ request('aksi') == 'tambah' ? 'selected' : '' }}>Tambah</option>
                        <option value="update" {{ request('aksi') == 'update' ? 'selected' : '' }}>Update</option>
                        <option value="hapus" {{ request('aksi') == 'hapus' ? 'selected' : '' }}>Hapus</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="tanggal">Tanggal:</label>
                    <input type="date" 
                           id="tanggal" 
                           name="tanggal" 
                           value="{{ request('tanggal') }}" 
                           class="form-control">
                </div>
            </div>
            
            <div class="filter-buttons">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                <a href="{{ route('history') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    @if(request()->hasAny(['nama_barang', 'nama_karyawan', 'aksi', 'tanggal']))
    <div class="results-info">
        <p>Menampilkan {{ $histories->total() }} hasil 
           @if(request('nama_barang'))
               untuk barang "{{ request('nama_barang') }}"
           @endif
           @if(request('nama_karyawan'))
               oleh karyawan "{{ request('nama_karyawan') }}"
           @endif
           @if(request('aksi'))
               dengan aksi "{{ ucfirst(request('aksi')) }}"
           @endif
           @if(request('tanggal'))
               pada tanggal {{ \Carbon\Carbon::parse(request('tanggal'))->format('d-m-Y') }}
           @endif
        </p>
    </div>
    @endif
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Nama Barang</th>
                <th>Nama Karyawan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($histories as $history)
            <tr>
                <td>{{ $history->created_at->format('d-m-Y H:i') }}</td>
                <td>{{ $history->nama_barang ?? '-' }}</td>
                <td>{{ $history->karyawan->username ?? '-' }}</td>
                <td>
                    <span class="badge badge-{{ $history->aksi }}">
                        {{ ucfirst($history->aksi) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Tidak ada data yang ditemukan</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Pagination -->
    @if ($histories->hasPages())
        <div class="pagination-container">
            <div class="pagination">
                {{-- Tombol Sebelumnya --}}
                @if ($histories->onFirstPage())
                    <span>«</span>
                @else
                    <a href="{{ $histories->previousPageUrl() }}">«</a>
                @endif

                {{-- Nomor Halaman --}}
                @foreach ($histories->getUrlRange(1, $histories->lastPage()) as $page => $url)
                    @if ($page == $histories->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Tombol Selanjutnya --}}
                @if ($histories->hasMorePages())
                    <a href="{{ $histories->nextPageUrl() }}">»</a>
                @else
                    <span>»</span>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@vite(['resources/css/history.css', 'resources/js/history.js'])