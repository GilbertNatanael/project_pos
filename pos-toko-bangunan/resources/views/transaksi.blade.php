@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection

@section('content')
<div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand">Transaksi</a>
        </div>
    </nav>

    <div class="sales-container mt-3">
        <div class="row">
            <div class="col-md-8">
                {{-- Kartu Informasi Transaksi --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="date">Date</label>
                                <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}">

                                <label for="time" class="mt-3">Time (Opsional)</label>
                                <input type="time" class="form-control" id="time" name="time" placeholder="Kosongkan untuk waktu saat ini">
                                <small class="text-muted">Biarkan kosong untuk menggunakan waktu saat transaksi dilakukan</small>

                                <label for="kasir" class="mt-3">Kasir</label>
                                <input type="text" class="form-control" id="kasir" name="kasir" value="{{ Session::get('username') }}" readonly>
                            </div>
                            <!-- Dalam bagian form Payment Method, tambahkan setelah select payment method -->
                            <div class="col-md-6">
                                <label for="paymentMethod">Payment Method</label>
                                <select class="form-select" id="paymentMethod" name="paymentMethod">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="qris">QRIS</option>
                                </select>
                                
                                <!-- Field Bank - Hidden by default -->
                                <div id="formBank" class="mt-3" style="display: none;">
                                    <label for="bank">Bank</label>
                                    <select class="form-select" id="bank" name="bank">
                                        <option value="">Pilih Bank</option>
                                        <option value="BCA">BCA</option>
                                        <option value="BRI">BRI</option>
                                        <option value="BNI">BNI</option>
                                        <option value="Mandiri">Mandiri</option>
                                        <option value="CIMB">CIMB Niaga</option>
                                        <option value="Danamon">Danamon</option>
                                        <option value="Permata">Permata</option>
                                        <option value="BTN">BTN</option>
                                        <option value="BSI">BSI</option>
                                        <option value="Muamalat">Muamalat</option>
                                    </select>
                                </div>
                                
                                <!-- Field Nomor Rekening - Hidden by default -->
                                <div id="formRekening" class="mt-3" style="display: none;">
                                    <label for="nomorRekening">Nomor Rekening</label>
                                    <input type="text" class="form-control" id="nomorRekening" name="nomorRekening" placeholder="Masukkan nomor rekening">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabel Barang Transaksi --}}
                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-success mb-3" id="btnAdd">Tambah Barang</button>

                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th>Kategori</th>
                                    <th>Merek</th>
                                    <th>Harga</th>
                                    <th>Satuan</th>
                                    <th>Pcs</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="salesTable">
                                {{-- Isi dari JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Sidebar Ringkasan --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="total-display h3 text-center mb-3">0</div>
                        <div class="form-group mb-2">
                            <label>Sub Total</label>
                            <input type="text" class="form-control" id="subTotal" value="0" readonly>
                        </div>
                        <div class="form-group mb-2">
                            <label>Grand Total</label>
                            <input type="text" class="form-control" id="grandTotal" value="0" readonly>
                        </div>
                        <div class="form-group mb-2" id="formCash">
                            <label>Cash</label>
                            <input type="text" class="form-control" id="cash">
                        </div>
                        <div class="form-group mb-2" id="formChange">
                            <label>Change</label>
                            <input type="text" class="form-control" id="change" value="0" readonly>
                        </div>
                        <div class="form-group mb-2">
                            <label>Note</label>
                            <textarea class="form-control" id="note" rows="3"></textarea>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-danger w-100 mb-2 btn-cancel"><i class="fas fa-times"></i> Cancel</button>
                            <button class="btn btn-success w-100 btn-process"><i class="fas fa-check"></i> Process Payment</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Pilih Barang --}}
<div class="modal fade" id="barangModal" tabindex="-1" aria-labelledby="barangModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> {{-- Ganti dari modal-lg ke modal-xl untuk width lebih besar --}}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeModal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari nama atau kode barang...">
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Merek</th>
                            <th>Harga</th>
                            <th>Satuan</th>
                            <th>Stok</th>
                            <th>Qty</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabelBarang">
                        @foreach($barang as $item)
                        <tr data-id="{{ $item->id_barang }}" 
                            data-nama="{{ $item->nama_barang }}" 
                            data-harga="{{ $item->harga_barang }}" 
                            data-satuan="{{ $item->satuan_barang ?? 'pcs' }}"
                            data-stok="{{ $item->jumlah_barang }}"
                            data-kategori="{{ $item->kategori->nama_kategori ?? '-' }}"
                            data-merek="{{ $item->merek ?? '-' }}">
                            <td>{{ $item->kode_barang }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
                            <td>{{ $item->merek ?? '-' }}</td>
                            <td>{{ number_format($item->harga_barang) }}</td>
                            <td>{{ $item->satuan_barang ?? 'pcs' }}</td>
                            <td>{{ $item->jumlah_barang }}</td>
                            <td class="text-center">
                                <input type="number" class="form-control qty-input d-inline-block" 
                                    value="1" min="0.01" step="0.01" max="{{ $item->jumlah_barang }}" 
                                    style="width: 80px; display: inline-block;">
                            </td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-add-barang">Tambah</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal Menunggu Pembayaran -->
<div class="modal fade" id="menungguPembayaranModal" tabindex="-1" aria-labelledby="menungguPembayaranLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-center">
        <div class="modal-header">
          <h5 class="modal-title w-100" id="menungguPembayaranLabel">Menunggu Pembayaran</h5>
        </div>
        <div class="modal-body">
          <p>Silakan tunggu hingga pembayaran selesai...</p>
          <div class="d-flex justify-content-center gap-3 mt-4">
            <button type="button" class="btn btn-success btn-selesai-pembayaran">Selesai</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          </div>
        </div>
      </div>
    </div>
  </div>
<!-- Modal Preview Struk -->
<div class="modal fade" id="previewStrukModal" tabindex="-1" aria-labelledby="previewStrukLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewStrukLabel">Preview Struk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                <div id="strukContent" class="text-center" style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; border: 1px solid #dee2e6;">
                    <!-- Isi struk akan diisi oleh JavaScript -->
                    <p>Loading...</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" id="btnPrintStruk">
                    <i class="fas fa-print"></i> Print Struk
                </button>
                <button type="button" class="btn btn-success" id="btnSelesaiStruk">
                    <i class="fas fa-check"></i> Selesai
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@vite(['resources/css/transaksi.css', 'resources/js/transaksi.js'])
@endsection