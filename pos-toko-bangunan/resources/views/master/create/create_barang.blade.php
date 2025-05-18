@extends('layouts.app')

@section('content')
<a href="{{ route('barang.index') }}" class="btn btn-secondary kembali-btn">
    <i class="bi bi-arrow-left"></i> Kembali
</a>
<div class="container">
    <h2 class="text-center heading-create">Create Barang</h2>

    <form action="{{ route('barang.store') }}" method="POST" class="form-container" id="createBarangForm">
        @csrf

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

        <div class="button-container">
            <button type="submit" class="btn btn-save" id="submitButton">Simpan</button>
        </div>
    </form>
</div>
@endsection

@vite(['resources/css/create_barang.css','resources/js/create_barang.js'])
