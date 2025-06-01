<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Detail</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4472C4;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #4472C4;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header .periode {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        
        .header .tanggal-cetak {
            font-size: 12px;
            color: #888;
        }
        
        .transaksi-card {
            margin-bottom: 25px;
            border: 2px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            break-inside: avoid;
        }
        
        .transaksi-header {
            background-color: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        
        .transaksi-header .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .transaksi-header .row:last-child {
            margin-bottom: 0;
        }
        
        .transaksi-info {
            display: inline-block;
            margin-right: 25px;
        }
        
        .transaksi-info strong {
            color: #4472C4;
        }
        
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        
        .detail-table th {
            background-color: #e9ecef;
            padding: 10px 8px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: bold;
            color: #495057;
        }
        
        .detail-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .detail-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .detail-table tr:hover {
            background-color: #e3f2fd;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-row {
            background-color: #d4edda !important;
            font-weight: bold;
            border-top: 2px solid #28a745;
        }
        
        .summary {
            margin-top: 30px;
            padding: 20px;
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
        }
        
        .summary h3 {
            margin: 0 0 15px 0;
            color: #856404;
            font-size: 18px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .summary-item:last-child {
            margin-bottom: 0;
            font-weight: bold;
            font-size: 16px;
            border-top: 1px solid #856404;
            padding-top: 8px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
        
        @media print {
            .transaksi-card {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TRANSAKSI DETAIL</h1>
        <div class="periode">
            Periode: 
            @if(request('start_date') && request('end_date'))
                {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
            @elseif(request('start_date'))
                Mulai {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }}
            @elseif(request('end_date'))
                Sampai {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
            @else
                Semua Data
            @endif
        </div>
        <div class="tanggal-cetak">Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>

    @if($transaksi->count() > 0)
        @php
            $totalKeseluruhan = 0;
            $totalTransaksi = $transaksi->count();
        @endphp

        @foreach($transaksi as $t)
            @php $totalKeseluruhan += $t->total_harga; @endphp
            
            <div class="transaksi-card">
                <div class="transaksi-header">
                    <div class="row">
                        <div>
                            <span class="transaksi-info">
                                <strong>ID:</strong> {{ $t->id_transaksi }}
                            </span>
                            <span class="transaksi-info">
                                <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($t->tanggal_waktu)->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <div>
                            <span class="transaksi-info">
                                <strong>Metode:</strong> {{ $t->metode_pembayaran }}
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <span class="transaksi-info">
                                <strong>Catatan:</strong> {{ $t->note ?: '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="transaksi-info">
                                <strong>Total:</strong> Rp{{ number_format($t->total_harga, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($t->detailTransaksi->count() > 0)
                    <table class="detail-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="45%">Nama Barang</th>
                                <th width="15%" class="text-center">Jumlah</th>
                                <th width="17%" class="text-right">Harga Satuan</th>
                                <th width="18%" class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($t->detailTransaksi as $detail)
                                @php
                                    $hargaSatuan = $detail->jumlah > 0 ? $detail->subtotal / $detail->jumlah : 0;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $no++ }}</td>
                                    <td>{{ $detail->barang->nama_barang ?? 'Barang Tidak Ditemukan' }}</td>
                                    <td class="text-center">{{ $detail->jumlah }}</td>
                                    <td class="text-right">Rp{{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                                    <td class="text-right">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td colspan="4" class="text-right"><strong>TOTAL TRANSAKSI:</strong></td>
                                <td class="text-right"><strong>Rp{{ number_format($t->total_harga, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                @else
                    <div style="padding: 20px; text-align: center; color: #6c757d;">
                        <em>Tidak ada detail barang untuk transaksi ini</em>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="summary">
            <h3>RINGKASAN LAPORAN</h3>
            <div class="summary-item">
                <span>Total Transaksi:</span>
                <span>{{ $totalTransaksi }} transaksi</span>
            </div>
            <div class="summary-item">
                <span>Periode Laporan:</span>
                <span>
                    @if(request('start_date') && request('end_date'))
                        {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
                    @else
                        Semua Data
                    @endif
                </span>
            </div>
            @if(request('metode'))
                <div class="summary-item">
                    <span>Metode Pembayaran:</span>
                    <span>{{ request('metode') }}</span>
                </div>
            @endif
            <div class="summary-item">
                <span><strong>TOTAL KESELURUHAN:</strong></span>
                <span><strong>Rp{{ number_format($totalKeseluruhan, 0, ',', '.') }}</strong></span>
            </div>
        </div>
    @else
        <div class="no-data">
            <h3>Tidak ada data transaksi yang ditemukan</h3>
            <p>Silakan coba dengan filter yang berbeda atau periksa kembali kriteria pencarian Anda.</p>
        </div>
    @endif
</body>
</html>