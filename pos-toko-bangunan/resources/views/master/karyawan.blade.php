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
        <h1 class="text-2xl font-bold">Master Karyawan</h1>
        <div class="add-button-container">
            <a href="{{ route('create_karyawan') }}" class="add-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5"></path>
                </svg>
                Tambah Karyawan
            </a>
        </div>
    </div>

    <div class="flex items-center gap-4 mb-4">
        <form method="GET" action="{{ route('karyawan.index') }}" class="flex items-center gap-2 w-full">
            <input type="text" name="search" placeholder="Cari username..." value="{{ request('search') }}" class="border border-gray-300 rounded px-3 py-2 w-full" />
            <select name="filter" class="border border-gray-300 rounded px-3 py-2">
                <option value="">Semua</option>
                <option value="owner" {{ request('filter') == 'owner' ? 'selected' : '' }}>owner</option>
                <option value="karyawan" {{ request('filter') == 'karyawan' ? 'selected' : '' }}>karyawan</option>
            </select>
            <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">Cari</button>
        </form>
    </div>

    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-2">Username</th>
                    <th class="text-left px-4 py-2">Role</th>
                    <th class="text-left px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($karyawan as $item)
                    <tr data-row="{{ $item->id_karyawan }}">
                        <td class="px-4 py-2">{{ $item->username }}</td>
                        <td class="px-4 py-2 capitalize">{{ $item->role }}</td>
                        <td class="px-4 py-2">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <a href="#" class="edit-button"
                                data-id="{{ $item->id_karyawan }}"
                                data-username="{{ $item->username }}"
                                data-role="{{ $item->role }}">
                                Edit
                             </a>
                             
            
                                <form action="{{ route('karyawan.destroy', $item->id_karyawan) }}" 
                                      method="POST" onsubmit="return confirm('Yakin hapus karyawan ini?')"
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
                        <td colspan="3" class="px-4 py-4 text-center text-gray-500">Tidak ada data karyawan.</td>
                    </tr>
                @endforelse
            </tbody>
            
        </table>
    </div>

    <div class="mt-4">
        {{ $karyawan->links() }}
    </div>
</div>
@endsection

@vite(['resources/css/karyawan.css', 'resources/js/karyawan.js'])


