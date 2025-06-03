@extends('layouts.app')

@section('content')
<div class="container-fluid tambah-pembelian-container mt-4">
    <div class="row">
        <!-- Kiri: Cari & Tambah Barang -->
        <div class="col-md-7">
        <a href="{{ route('pembelian') }}" class="btn btn-secondary">
        ← batal
        </a>
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Pilih Barang</div>
                <div class="card-body">
                    <div class="mb-3 d-flex gap-3">
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari barang...">
                        <select class="form-select" id="filterKategori">
                            <option value="">Semua Kategori</option>
                            <option value="bangunan">Bangunan</option>
                            <option value="makanan">Makanan</option>
                        </select>
                    </div>
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Stok</th>
                                <th>Satuan</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                            <tbody id="daftarBarang">
                                @foreach ($barang as $b)
                                    <tr data-id="{{ $b->id_barang }}" data-nama="{{ $b->nama_barang }}" data-harga="{{ $b->harga_barang }}" data-satuan="{{ $b->satuan_barang }}">
                                        <td>{{ $b->kode_barang }}</td>
                                        <td>{{ $b->nama_barang }}</td>
                                        <td>{{ $b->jumlah_barang }}</td>
                                        <td>{{ $b->satuan_barang }}</td>
                                        <td>Rp {{ number_format($b->harga_barang, 0, ',', '.') }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-success btn-tambah-barang">Tambah</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kanan: Keranjang Pembelian -->
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white fw-bold">Daftar Pembelian</div>
                <div class="card-body">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="keranjangPembelian">
                            <!-- Dinamis via JS -->
                        </tbody>
                    </table>

                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold" id="totalHarga">Rp 0</span>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-danger" id="btnResetPembelian">Reset</button>
                        <button class="btn btn-primary" id="btnSimpanPembelian">Simpan Pembelian</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
@endsection

@vite(['resources/css/tambah_pembelian.css', 'resources/js/tambah_pembelian.js'])