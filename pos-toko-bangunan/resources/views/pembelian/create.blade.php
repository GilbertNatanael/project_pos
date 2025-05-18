@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">Pembelian Barang</h1>
    <form action="{{ route('pembelian.store') }}" method="POST">
        @csrf

        <table class="min-w-full table-auto mb-4">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($barang as $b)
                <tr>
                    <td>
                        <input type="hidden" name="id_barang[]" value="{{ $b->id_barang }}">
                        {{ $b->nama_barang }}
                    </td>
                    <td>
                        <input type="number" name="jumlah[]" class="border rounded px-2 py-1" min="0" value="0">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan Pembelian</button>
    </form>
</div>
@endsection
