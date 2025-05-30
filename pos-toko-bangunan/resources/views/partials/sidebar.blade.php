<!-- resources/views/partials/sidebar.blade.php -->
<div class="sidebar">
    <div class="sidebar-header">POS System</div>
    
    <ul class="sidebar-menu">
        @if(session('role') === 'owner')
        <li><a href="{{ route('dashboard') }}" class="{{ request()->is('dashboard*') ? 'active' : '' }}"><i>📊</i> Dashboard</a></li>
        @endif

        <li class="dropdown {{ request()->is('master/*') ? 'active' : '' }}">
            <a href="#"><i>🛠️</i> Master ▾</a>
            <ul class="dropdown-menu">
                <li><a href="{{ route('barang') }}" class="{{ request()->is('master/barang') ? 'active' : '' }}">📦 Barang</a></li>
                @if(session('role') === 'owner')
                <li><a href="{{ route('karyawan') }}" class="{{ request()->is('master/karyawan') ? 'active' : '' }}">👤 Karyawan</a></li>
                @endif
            </ul>
        </li>

        <li><a href="{{ route('transaksi') }}" class="{{ request()->is('transaksi*') ? 'active' : '' }}"><i>💰</i> Transaksi</a></li>

        @if(session('role') === 'owner')
        <li><a href="{{ route('pembelian') }}" class="{{ request()->is('pembelian/pembelian') ? 'active' : '' }}"><i>🛒</i> Pembelian</a></li>
        <li><a href="{{ route('prediksi') }}" class="{{ request()->is('prediksi*') ? 'active' : '' }}"><i>📈</i> Prediksi</a></li>
        <li><a href="{{ route('laporan') }}" class="{{ request()->is('laporan*') ? 'active' : '' }}"><i>📋</i> Laporan</a></li>
        <li><a href="{{ route('history') }}" class="{{ request()->is('history*') ? 'active' : '' }}"><i>🕘</i> History</a></li>
        @endif
    </ul>

    <div class="logout">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link text-danger p-0"><i>🚪</i> Logout</button>
        </form>
    </div>
</div>

@vite(['resources/css/sidebar.css', 'resources/js/sidebar.js'])
