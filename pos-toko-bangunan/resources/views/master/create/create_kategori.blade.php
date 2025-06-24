@extends('layouts.app')

@section('content')
<a href="{{ route('kategori') }}" class="btn btn-secondary kembali-btn">
    <i class="bi bi-arrow-left"></i> Kembali
</a>
<div class="page-container">
    <div class="form-wrapper">
        <h2 class="text-center heading-create">Create Kategori</h2>

        <form action="{{ route('kategori.store') }}" method="POST" class="form-container" id="createKategoriForm">
            @csrf
            <div class="form-group">
                <label for="nama_kategori">Nama Kategori</label>
                <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" placeholder="Masukkan nama kategori" value="{{ old('nama_kategori') }}" required>
                @error('nama_kategori')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="button-container">
                <button type="submit" class="btn btn-save" id="submitButton">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
@vite(['resources/css/create_kategori.css', 'resources/js/create_kategori.js'])