@extends('layouts.app')

@section('content')
<a href="{{ route('karyawan.index') }}" class="btn btn-secondary kembali-btn">
    <i class="bi bi-arrow-left"></i> Kembali
</a>
<div class="container">
    <h2 class="text-center heading-create">Create Karyawan</h2>

    <form action="{{ route('karyawan.store') }}" method="POST" class="form-container" id="createKaryawanForm">
        @csrf

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan Username" value="{{ old('username') }}" required>
            @error('username')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password" required>
            @error('password')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select class="form-control" id="role" name="role" required>
                <option value="">Pilih Role</option>
                <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                <option value="karyawan" {{ old('role') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
            </select>
            @error('role')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="button-container">
            <button type="submit" class="btn btn-save" id="submitButton">Simpan</button>
        </div>
    </form>
</div>
@endsection

@vite(['resources/css/create_barang.css', 'resources/js/create_barang.js'])
