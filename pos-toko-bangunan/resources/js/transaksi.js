$(document).ready(function () {
    // ==================== Function untuk Format Rupiah ====================
    function formatRupiah(angka) {
        if (!angka || isNaN(angka)) return 'Rp 0';
        return 'Rp ' + parseInt(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Function untuk parsing angka dari format Rupiah
    function parseRupiah(rupiahString) {
        if (!rupiahString) return 0;
        return parseInt(rupiahString.toString().replace(/[Rp\s.]/g, '')) || 0;
    }

    // ==================== Perhitungan Total dengan Format Rupiah ====================
    function calculateTotals() {
        let subTotal = 0;
        $('#salesTable tr').each(function () {
            const totalText = $(this).find('td:eq(6)').text();
            const total = parseRupiah(totalText);
            subTotal += total;
        });

        const cashInput = $('#cash').val();
        const cash = parseRupiah(cashInput);
        const grandTotal = subTotal;
        const change = cash - grandTotal;

        // Update dengan format Rupiah
        $('#subTotal').val(formatRupiah(subTotal));
        $('#grandTotal').val(formatRupiah(grandTotal));
        $('#change').val(formatRupiah(change));
        $('.total-display').text(formatRupiah(grandTotal));

        // Simpan nilai numerik untuk perhitungan
        $('#subTotal').data('numeric', subTotal);
        $('#grandTotal').data('numeric', grandTotal);
        $('#change').data('numeric', change);
    }

    function updateNumbering() {
        $('#salesTable tr').each(function (index) {
            $(this).find('td:first').text((index + 1) + '.');
        });
    }

    // ==================== Fungsi Reset Lengkap ====================
    function resetAllTransaction() {
        // Reset tabel transaksi
        $('#salesTable').empty();
        
        // Reset semua input form
        $('#cash').val('');
        $('#note').val('');
        $('#subTotal').val(formatRupiah(0));
        $('#grandTotal').val(formatRupiah(0));
        $('#change').val(formatRupiah(0));
        
        // Reset display total
        $('.total-display').text(formatRupiah(0));
        
        // Reset payment method ke default (biasanya cash)
        $('#paymentMethod').val('cash');
        
        // Reset tanggal ke hari ini dan kosongkan waktu
        $('#date').val(new Date().toISOString().split('T')[0]);
        $('#time').val('');
        
        // Trigger change event untuk payment method (jika ada handler)
        $('#paymentMethod').trigger('change');
        
        // Reset quantity di modal barang ke 1
        $('#tabelBarang .qty-val').text('1');
        
        // Reset search dan filter di modal
        $('#searchInput').val('');
        $('#filterKategori').val('');
        $('#tabelBarang tr').show();
        
        // Recalculate totals (akan menghasilkan 0)
        calculateTotals();
    }

    // Format input cash saat user mengetik
    $('#cash').on('input', function() {
        const value = parseRupiah($(this).val());
        if (value > 0) {
            $(this).val(formatRupiah(value));
        }
        calculateTotals();
    });

    // ==================== Modal Barang ====================
    $('#btnAdd').click(function () {
        $('#searchInput').val('');
        $('#filterKategori').val('');
        $('#tabelBarang tr').show();
        $('#tabelBarang .qty-val').text('1');

        const barangModal = new bootstrap.Modal(document.getElementById('barangModal'));
        barangModal.show();
    });

    $('#searchInput').on('keyup', function () {
        const keyword = $(this).val().toLowerCase();
        $('#tabelBarang tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(keyword) > -1);
        });
    });

    $('#filterKategori').on('change', function () {
        const kategori = $(this).val().toLowerCase();
        if (kategori === '') {
            $('#tabelBarang tr').show();
        } else {
            $('#tabelBarang tr').each(function () {
                const rowKategori = $(this).data('kategori') ? $(this).data('kategori').toLowerCase() : '';
                $(this).toggle(rowKategori === kategori);
            });
        }
    });

    // ==================== Tambah Barang ke Tabel Transaksi ====================
    $(document).on('click', '.btn-add-barang', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const tr = $(this).closest('tr');
        const id = tr.data('id');
        const kode = tr.find('td:eq(0)').text();
        const nama = tr.data('nama');
        const harga = parseInt(tr.data('harga'));
        const satuan = tr.data('satuan') || 'pcs';
        const stok = parseInt(tr.data('stok'));
        const qty = parseFloat(tr.find('.qty-input').val()) || 1;
        const total = harga * qty;

        // Validasi quantity
        if (qty <= 0) {
            alert('Quantity harus lebih dari 0!');
            return;
        }

        if (qty > stok) {
            alert('Quantity melebihi stok yang tersedia!');
            return;
        }

        // Cek apakah barang sudah ada di #salesTable
        let exists = false;
        $('#salesTable tr').each(function () {
            const kodeBarang = $(this).find('td:eq(1)').text();
            if (kodeBarang === kode) {
                exists = true;
                return false;
            }
        });

        if (exists) {
            alert('Barang sudah ada di keranjang. Silakan update jumlahnya jika ingin mengubah.');
            return;
        }

        const newRow = `
        <tr data-id="${id}" data-stok="${stok}" data-satuan="${satuan}">
            <td>#</td>
            <td>${kode}</td>
            <td>${nama}</td>
            <td>${formatRupiah(harga)}</td>
            <td>${satuan}</td>
            <td>${qty}</td>
            <td>${formatRupiah(total)}</td>
            <td>
                <button class="btn btn-sm btn-warning btn-update"><i class="fas fa-edit"></i> Update</button>
                <button class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i> Delete</button>
            </td>
        </tr>
    `;
    
        $('#salesTable').append(newRow);
        updateRowNumbers();
        calculateTotals();

        // Reset quantity input ke 1
        tr.find('.qty-input').val(1);

        const barangModal = bootstrap.Modal.getInstance(document.getElementById('barangModal'));
        barangModal.hide();
    });

    // ==================== Hapus Barang dari Tabel ====================
    $(document).on('click', '.btn-delete', function () {
        $(this).closest('tr').remove();
        updateRowNumbers();
        calculateTotals();
    });

    // ==================== Edit (Update) Barang di Tabel ====================
    $(document).on('click', '.btn-update', function () {
        const row = $(this).closest('tr');
        const kode = row.find('td:eq(1)').text();
        const nama = row.find('td:eq(2)').text();
        const hargaText = row.find('td:eq(3)').text();
        const harga = parseRupiah(hargaText);
        const satuan = row.find('td:eq(4)').text();
        const qty = row.find('td:eq(5)').text();
        const totalText = row.find('td:eq(6)').text();
        const total = parseRupiah(totalText);
        const stok = row.data('stok');

        const formRow = `
            <tr class="edit-row">
                <td>#</td>
                <td>${kode}</td>
                <td>${nama}</td>
                <td><input type="text" class="form-control form-harga" value="${formatRupiah(harga)}"></td>
                <td>${satuan}</td>
                <td><input type="number" class="form-control form-qty" value="${qty}" min="1" max="${stok}"></td>
                <td><span class="form-total">${formatRupiah(total)}</span></td>
                <td>
                    <input type="hidden" class="form-stok" value="${stok}">
                    <button class="btn btn-sm btn-save">Simpan</button>
                    <button class="btn btn-sm btn-cancel-edit">Batal</button>
                </td>
            </tr>
        `;

        row.hide();
        row.after(formRow);
    });

    // Handler untuk format harga saat edit
    $(document).on('input', '.form-harga', function() {
        const value = parseRupiah($(this).val());
        if (value > 0) {
            $(this).val(formatRupiah(value));
        }
        updateFormTotal($(this).closest('tr'));
    });

    $(document).on('input', '.form-qty', function() {
        updateFormTotal($(this).closest('tr'));
    });

    function updateFormTotal(formRow) {
        const hargaText = formRow.find('.form-harga').val();
        const harga = parseRupiah(hargaText);
        const qty = parseInt(formRow.find('.form-qty').val()) || 0;
        const total = harga * qty;
        formRow.find('.form-total').text(formatRupiah(total));
    }

    $(document).on('click', '.btn-save', function () {
        const formRow = $(this).closest('tr');
        const hargaText = formRow.find('.form-harga').val();
        const harga = parseRupiah(hargaText);
        const qty = parseInt(formRow.find('.form-qty').val());
        const stok = parseInt(formRow.find('.form-stok').val());

        if (qty > stok) {
            alert('Jumlah melebihi stok yang tersedia!');
            return;
        }

        const total = harga * qty;

        const originalRow = formRow.prev();
        originalRow.find('td:eq(3)').text(formatRupiah(harga));
        originalRow.find('td:eq(5)').text(qty);
        originalRow.find('td:eq(6)').text(formatRupiah(total));

        formRow.remove();
        originalRow.show();

        calculateTotals();
    });

    $(document).on('click', '.btn-cancel-edit', function () {
        const formRow = $(this).closest('tr');
        const originalRow = formRow.prev();
        formRow.remove();
        originalRow.show();
    });

    // ==================== Toggle Input Cash/Change ====================
    function toggleCashFields() {
        const paymentMethod = $('#paymentMethod').val();
        if (paymentMethod === 'cash') {
            $('#formCash, #formChange').show();
        } else {
            $('#formCash, #formChange').hide();
        }
    }

    toggleCashFields();
    $('#paymentMethod').on('change', toggleCashFields);

    // ==================== Proses dan Batal ====================
    $('.btn-process').click(function () {
        const metodePembayaran = $('#paymentMethod').val();
        const grandTotal = $('#grandTotal').data('numeric') || 0;
        const cashInput = $('#cash').val();
        const cash = parseRupiah(cashInput);
    
        if (metodePembayaran === 'cash') {
            if (!cash || cash < grandTotal) {
                alert('Uang tunai tidak cukup untuk membayar total belanja!');
                return;
            }
        }
    
        const pembayaranModal = new bootstrap.Modal(document.getElementById('menungguPembayaranModal'));
        pembayaranModal.show();
    });
    
    function getCartItems() {
        const items = [];
        const note = $('#note').val();
    
        $('#salesTable tr').each(function () {
            const kodeBarang = $(this).find('td:eq(1)').text();
            const namaBarang = $(this).find('td:eq(2)').text();
            const hargaText = $(this).find('td:eq(3)').text();
            const hargaBarang = parseRupiah(hargaText);
            const satuanBarang = $(this).find('td:eq(4)').text();
            const jumlah = parseInt($(this).find('td:eq(5)').text());
            const subtotalText = $(this).find('td:eq(6)').text();
            const subtotal = parseRupiah(subtotalText);
    
            const idBarang = $(this).data('id');
    
            if (idBarang && jumlah > 0) {
                items.push({
                    id_barang: idBarang,
                    kode_barang: kodeBarang,
                    nama_barang: namaBarang,
                    harga_barang: hargaBarang,
                    satuan_barang: satuanBarang,
                    jumlah: jumlah,
                    subtotal: subtotal,
                    note: note
                });
            }
        });
    
        return items;
    }
    
    $('.btn-selesai-pembayaran').click(function () {
        console.log('Tombol selesai pembayaran diklik');
        
        const items = getCartItems();
        const metodePembayaran = $('#paymentMethod').val();
        const totalHarga = $('#grandTotal').data('numeric') || 0;
        const note = $('#note').val();
        const tanggalTransaksi = $('#date').val();
        const waktuTransaksi = $('#time').val();
    
        console.log('Data transaksi:', { items, metodePembayaran, totalHarga, note, tanggalTransaksi, waktuTransaksi });
    
        if (items.length === 0) {
            alert('Keranjang kosong!');
            return;
        }

        if (!tanggalTransaksi) {
            alert('Tanggal transaksi harus diisi!');
            return;
        }
    
        $.ajax({
            url: '/transaksi/store',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                metode_pembayaran: metodePembayaran,
                total_harga: totalHarga,
                tanggal_transaksi: tanggalTransaksi,
                waktu_transaksi: waktuTransaksi,
                items: items,
                note: note
            },
            success: function (response) {
                console.log('Response dari server:', response);
                
                const modalMenunggu = bootstrap.Modal.getInstance(document.getElementById('menungguPembayaranModal'));
                if (modalMenunggu) {
                    modalMenunggu.hide();
                }
    
                setTimeout(function() {
                    generatePreviewStruk(response.transaksi, items);
                    
                    console.log('Mencoba menampilkan modal preview');
                    
                    const modalPreview = new bootstrap.Modal(document.getElementById('previewStrukModal'));
                    modalPreview.show();
                }, 500);
            },
    
            error: function (xhr) {
                console.log('Error response:', xhr);
                alert(xhr.responseJSON?.message || 'Gagal menyimpan transaksi.');
            }
        });
    });

    // Function untuk generate preview struk dengan format Rupiah
    function generatePreviewStruk(transaksi, items) {
        console.log('Generating preview struk dengan data:', transaksi, items);
        
        const tanggalWaktu = new Date(transaksi.tanggal_waktu);
        const tanggalFormat = tanggalWaktu.toLocaleDateString('id-ID');
        const waktuFormat = tanggalWaktu.toLocaleTimeString('id-ID');
        const kasir = $('#kasir').val();
        const metodePembayaran = $('#paymentMethod').val().toUpperCase();
        const cashInput = $('#cash').val();
        const cash = parseRupiah(cashInput);
        const change = $('#change').data('numeric') || 0;

        let strukHtml = `
            <div style="max-width: 300px; margin: 0 auto; text-align: left; font-family: 'Courier New', monospace;">
                <div style="text-align: center; margin-bottom: 15px;">
                    <strong>Toko Bangunan</strong><br>
                    Jl. Contoh No. 123<br>
                    Telp: 021-12345678<br>
                    <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>
                </div>
                
                <div style="margin-bottom: 10px;">
                    <strong>No. Transaksi: ${transaksi.id_transaksi}</strong><br>
                    Tanggal: ${tanggalFormat}<br>
                    Waktu: ${waktuFormat}<br>
                    Kasir: ${kasir}<br>
                    <div style="border-bottom: 1px dashed #000; margin: 10px 0;"></div>
                </div>
                
                <div style="margin-bottom: 10px;">
        `;

        // Loop items dengan format Rupiah
        items.forEach(function(item) {
            strukHtml += `
                    <div style="margin-bottom: 8px;">
                        <div><strong>${item.nama_barang}</strong></div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>${item.jumlah} ${item.satuan_barang} x ${formatRupiah(item.harga_barang)}</span>
                            <span>${formatRupiah(item.subtotal)}</span>
                        </div>
                    </div>
            `;
        });

        strukHtml += `
                </div>
                
                <div style="border-top: 1px dashed #000; padding-top: 10px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Sub Total:</span>
                        <span>${formatRupiah(transaksi.total_harga)}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 5px;">
                        <span>TOTAL:</span>
                        <span>${formatRupiah(transaksi.total_harga)}</span>
                    </div>
                </div>
                
                <div style="margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Pembayaran:</span>
                        <span>${metodePembayaran}</span>
                    </div>
        `;

        if (metodePembayaran === 'CASH') {
            strukHtml += `
                    <div style="display: flex; justify-content: space-between;">
                        <span>Tunai:</span>
                        <span>${formatRupiah(cash)}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Kembali:</span>
                        <span>${formatRupiah(change)}</span>
                    </div>
            `;
        }

        if (transaksi.note && transaksi.note.trim() !== '') {
            strukHtml += `
                    <div style="margin-top: 10px; padding-top: 5px; border-top: 1px solid #ccc;">
                        <strong>Catatan:</strong><br>
                        <span>${transaksi.note}</span>
                    </div>
            `;
        }

        strukHtml += `
                </div>
                
                <div style="text-align: center; margin-top: 15px; border-top: 1px dashed #000; padding-top: 10px;">
                    <strong>*** TERIMA KASIH ***</strong><br>
                    Barang yang sudah dibeli<br>
                    tidak dapat dikembalikan
                </div>
            </div>
        `;

        console.log('HTML struk yang akan ditampilkan:', strukHtml);
        $('#strukContent').html(strukHtml);
    }

    // Handler untuk tombol print struk
    $(document).on('click', '#btnPrintStruk', function() {
        console.log('Tombol print diklik');
        alert('Fitur print akan segera tersedia!');
    });

    // Handler untuk tombol selesai struk
    $(document).on('click', '#btnSelesaiStruk', function() {
        console.log('Tombol selesai struk diklik');
        
        const modalPreview = bootstrap.Modal.getInstance(document.getElementById('previewStrukModal'));
        if (modalPreview) {
            modalPreview.hide();
        }
        
        resetAllTransaction();
        
        alert('Transaksi berhasil diselesaikan!');
    });

    // ==================== Tombol Cancel - Reset Semua ====================
    $(document).on('click', '.btn-cancel', function () {
        if (confirm('Yakin ingin membatalkan transaksi? Semua data akan dihapus.')) {
            resetAllTransaction();
            
            alert('Transaksi telah dibatalkan dan semua data direset.');
        }
    });

    // Inisialisasi total awal
    calculateTotals();
});

// ==================== Function untuk Update Row Numbers ====================
function updateRowNumbers() {
    $('#salesTable tr').each(function (index) {
        $(this).find('td:first').text((index + 1) + '.');
    });
}

// ==================== Update Quantity di Modal ====================
$(document).on('click', '.btn-qty-minus', function () {
    const qtyElement = $(this).siblings('.qty-val');
    let currentQty = parseInt(qtyElement.text());
    if (currentQty > 1) {
        qtyElement.text(currentQty - 1);
    }
});

$(document).on('click', '.btn-qty-plus', function () {
    const tr = $(this).closest('tr');
    const stok = parseInt(tr.data('stok'));
    const qtyElement = $(this).siblings('.qty-val');
    let currentQty = parseInt(qtyElement.text());
    
    if (currentQty < stok) {
        qtyElement.text(currentQty + 1);
    } else {
        alert('Quantity tidak boleh melebihi stok yang tersedia!');
    }
});

// ==================== Handler untuk input quantity di modal ====================
$(document).on('change', '.qty-input', function () {
    const tr = $(this).closest('tr');
    const stok = parseInt(tr.data('stok'));
    let qty = parseFloat($(this).val());
    
    if (qty < 0.01) {
        $(this).val(0.01);
    } else if (qty > stok) {
        $(this).val(stok);
        alert('Quantity tidak boleh melebihi stok yang tersedia!');
    }
});