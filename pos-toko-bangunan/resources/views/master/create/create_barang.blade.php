@extends('layouts.app')

@section('content')
<a href="{{ route('barang') }}" class="btn btn-secondary kembali-btn">
    <i class="bi bi-arrow-left"></i> Kembali
</a>
<div class="page-container">
    <div class="form-wrapper">
        <h2 class="text-center heading-create">Create Barang</h2>

        <form action="{{ route('barang.store') }}" method="POST" class="form-container" id="createBarangForm">
            @csrf

            <div class="form-columns">
                <!-- Kolom Kiri -->
                <div class="column-left">
                    <div class="form-group">
                        <label for="kode_barang">Kode Barang</label>
                        <input type="text" class="form-control" id="kode_barang" name="kode_barang" placeholder="Masukkan Kode Barang" value="{{ old('kode_barang') }}" required>
                        @error('kode_barang')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="nama_barang">Nama Barang</label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" placeholder="Masukkan Nama Barang" value="{{ old('nama_barang') }}" required>
                        @error('nama_barang')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="merek">Merek Barang</label>
                        <input type="text" class="form-control" id="merek" name="merek" placeholder="Masukkan Merek Barang" value="{{ old('merek') }}">
                        @error('merek')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="kategori_id">Kategori</label>
                        <select class="form-control" id="kategori_id" name="kategori_id" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategori as $k)
                                <option value="{{ $k->id }}" {{ old('kategori_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="column-right">
                    <div class="form-group">
                        <label for="harga_barang">Harga Barang</label>
                        <input type="number" class="form-control" id="harga_barang" name="harga_barang" placeholder="Masukkan Harga Barang" value="{{ old('harga_barang') }}" required>
                        @error('harga_barang')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="jumlah_barang">Jumlah Barang</label>
                        <input type="number" class="form-control" id="jumlah_barang" name="jumlah_barang" placeholder="Masukkan Jumlah Barang" value="{{ old('jumlah_barang') }}" required>
                        @error('jumlah_barang')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="satuan_barang">Satuan Barang</label>
                        <select class="form-control" id="satuan_barang" name="satuan_barang" required>
                            <option value="">-- Pilih Satuan --</option>
                            <option value="pcs" {{ old('satuan_barang') == 'pcs' ? 'selected' : '' }}>pcs</option>
                            <option value="kg" {{ old('satuan_barang') == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="liter" {{ old('satuan_barang') == 'liter' ? 'selected' : '' }}>liter</option>
                            <option value="meter" {{ old('satuan_barang') == 'meter' ? 'selected' : '' }}>meter</option>
                            <option value="lusin" {{ old('satuan_barang') == 'lusin' ? 'selected' : '' }}>lusin</option>
                            <option value="batang" {{ old('satuan_barang') == 'batang' ? 'selected' : '' }}>batang</option>
                            <option value="zak" {{ old('satuan_barang') == 'zak' ? 'selected' : '' }}>zak</option>
                            <option value="lembar" {{ old('satuan_barang') == 'lembar' ? 'selected' : '' }}>lembar</option>
                        </select>
                        @error('satuan_barang')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="button-container">
                <button type="submit" class="btn btn-save" id="submitButton">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@vite(['resources/css/create_barang.css','resources/js/create_barang.js'])