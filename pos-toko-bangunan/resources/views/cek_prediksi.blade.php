@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="laporan-container">
    <h2 class="laporan-title">Cek Hasil Prediksi</h2>

{{-- Filter Tanggal dan Search --}}
<div class="card shadow-sm p-3 mb-4">
    <h5 class="mb-3"><i class="bi bi-funnel-fill"></i> Filter Hasil Prediksi</h5>
    <form id="filter-form" class="row g-3 align-items-end">
        {{-- Tanggal Prediksi Dibuat --}}
        <div class="col-md-3">
            <label for="filter-tanggal-start" class="form-label">Tanggal Prediksi (Dari)</label>
            <input type="date" id="filter-tanggal-start" class="form-control" placeholder="Mulai tanggal">
        </div>
        <div class="col-md-3">
            <label for="filter-tanggal-end" class="form-label">Tanggal Prediksi (Sampai)</label>
            <input type="date" id="filter-tanggal-end" class="form-control" placeholder="Sampai tanggal">
        </div>

        {{-- Periode Prediksi --}}
        <div class="col-md-3">
            <label for="periode-start" class="form-label">Periode Prediksi (Dari)</label>
            <input type="date" id="periode-start" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="periode-end" class="form-label">Periode Prediksi (Sampai)</label>
            <input type="date" id="periode-end" class="form-control">
        </div>

        {{-- Pencarian --}}
        <div class="col-md-6">
            <label for="search-bar" class="form-label">Cari ID Prediksi</label>
            <input type="text" id="search-bar" class="form-control" placeholder="Contoh: PRD-0059">
        </div>

        {{-- Tombol Aksi --}}
        <div class="col-md-6 d-flex gap-2 mt-2">
            <button type="button" class="btn btn-success w-100" id="filter-button">
                <i class="bi bi-search"></i> Terapkan Filter
            </button>
            <button type="button" class="btn btn-outline-secondary w-100" id="reset-button">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
        </div>
    </form>
</div>



    {{-- Tabel Prediksi (Hardcode) --}}
    <div class="table-wrapper">
        <table class="laporan-table">
            <thead>
                <tr>
                    <th>Kode Prediksi</th>
                    <th>Tanggal Prediksi</th>
                    <th>Jumlah Item</th>
                    <th>Periode</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
@endsection

@vite(['resources/css/cek_prediksi.css', 'resources/js/cek_prediksi.js'])
