@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Riwayat Aktivitas</h2>
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
            @foreach ($histories as $history)
            <tr>
                <td>{{ $history->created_at->format('d-m-Y H:i') }}</td>
                <td>{{ $history->barang->nama_barang ?? '-' }}</td>
                <td>{{ $history->karyawan->username ?? '-' }}</td>
                <td>{{ ucfirst($history->aksi) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $histories->links() }}
</div>
@endsection

@vite(['resources/css/history.css', 'resources/js/history.js'])
