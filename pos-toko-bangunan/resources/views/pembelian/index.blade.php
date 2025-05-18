@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">Riwayat Pembelian</h1>
    <a href="{{ route('pembelian.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mb-4 inline-block">+ Pembelian Baru</a>

    <div class="space-y-4">
        @foreach ($pembelian as $p)
        <div class="border rounded-lg p-4 shadow">
            <div class="text-lg font-semibold">Tanggal: {{ $p->tanggal_waktu }}</div>
            <table class="mt-2 w-full text-sm border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left">Nama Barang</th>
                        <th class="p-2 text-left">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($p->detail as $d)
                    <tr>
                        <td class="p-2">{{ $d->barang->nama_barang }}</td>
                        <td class="p-2">{{ $d->jumlah }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
</div>
@endsection
