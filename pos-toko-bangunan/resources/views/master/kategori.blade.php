@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    @if(session('success'))
        <div id="alert-success" class="alert alert-success">
            {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div id="alert-error" class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Master Kategori</h1>
        <div class="add-button-container">
            <a href="{{ route('create_kategori') }}" class="add-button">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"/>
                </svg>
                Tambah Kategori
            </a>
        </div>
    </div>

    <div class="flex items-center gap-4 mb-4">
        <form method="GET" action="{{ route('kategori') }}" class="flex items-center gap-2 w-full">
            <input type="text" name="search" placeholder="Cari kategori..." value="{{ request('search') }}"
                   class="border border-gray-300 rounded px-3 py-2 w-full" />
            <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">Cari</button>
        </form>
    </div>

    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-2">Nama Kategori</th>
                    <th class="text-left px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($kategori as $item)
                    <tr>
                        <td class="px-4 py-2">{{ $item->nama_kategori }}</td>
                        <td class="px-4 py-2">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <a href="#" class="edit-button"
                                data-nama_kategori="{{ $item->nama_kategori }}">
                                Edit
                             </a>
                                <form action="{{ route('kategori.destroy', $item->id) }}" 
                                      method="POST" onsubmit="return confirm('Yakin hapus kategori ini?')"
                                      style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-button">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center px-4 py-4 text-gray-500">Tidak ada data kategori.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $kategori->links() }}
    </div>
</div>
@endsection

@vite(['resources/css/kategori.css', 'resources/js/kategori.js'])
