$(document).ready(function () {
    // ==================== Perhitungan Total ====================
    function calculateTotals() {
        let subTotal = 0;
        $('#salesTable tr').each(function () {
            const total = parseInt($(this).find('td:eq(5)').text()) || 0;
            subTotal += total;
        });

        const cash = parseInt($('#cash').val()) || 0;
        const grandTotal = subTotal;
        const change = cash - grandTotal;

        $('#subTotal').val(subTotal);
        $('#grandTotal').val(grandTotal);
        $('#change').val(change);
        $('.total-display').text(grandTotal);
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
        $('#subTotal').val(0);
        $('#grandTotal').val(0);
        $('#change').val(0);
        
        // Reset display total
        $('.total-display').text("0");
        
        // Reset payment method ke default (biasanya cash)
        $('#paymentMethod').val('cash');
        
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

    $('#cash').on('input', calculateTotals);

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

    // ==================== Tombol Tambah/Kurang Qty di Modal ====================
    $(document).on('click', '.btn-increase', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const span = $(this).siblings('.qty-val');
        const tr = $(this).closest('tr');
        const stok = parseInt(tr.data('stok'));
        let val = parseInt(span.text());

        if (val < stok) {
            span.text(val + 1);
        } else {
            alert('Jumlah melebihi stok yang tersedia!');
        }
    });

    $(document).on('click', '.btn-decrease', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const span = $(this).siblings('.qty-val');
        let val = parseInt(span.text());
        if (val > 1) span.text(val - 1);
    });

    function updateRowNumbers() {
        $('#salesTable tr').each(function (index) {
            $(this).find('td:eq(0)').text((index + 1) + '.');
        });
    }

    // ==================== Tambah Barang ke Tabel Transaksi ====================
    $(document).on('click', '.btn-add-barang', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const tr = $(this).closest('tr');
        const id = tr.data('id');
        const kode = tr.find('td:eq(0)').text();
        const nama = tr.data('nama');
        const harga = parseInt(tr.data('harga'));
        const stok = parseInt(tr.data('stok'));
        const qty = parseInt(tr.find('.qty-val').text());
        const total = harga * qty;

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
        <tr data-id="${id}" data-stok="${stok}">
            <td>#</td>
            <td>${kode}</td>
            <td>${nama}</td>
            <td>${harga}</td>
            <td>${qty}</td>
            <td>${total}</td>
            <td>
                <button class="btn btn-sm btn-update"><i class="fas fa-edit"></i> Update</button>
                <button class="btn btn-sm btn-delete"><i class="fas fa-trash"></i> Delete</button>
            </td>
        </tr>
    `;
    
        $('#salesTable').append(newRow);
        updateRowNumbers();
        calculateTotals();

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
        const harga = row.find('td:eq(3)').text();
        const qty = row.find('td:eq(4)').text();
        const total = row.find('td:eq(5)').text();
        const stok = row.data('stok');

        const formRow = `
            <tr class="edit-row">
                <td>#</td>
                <td>${kode}</td>
                <td>${nama}</td>
                <td><input type="number" class="form-control form-harga" value="${harga}"></td>
                <td><input type="number" class="form-control form-qty" value="${qty}" min="1" max="${stok}"></td>
                <td><span class="form-total">${total}</span></td>
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

    $(document).on('click', '.btn-save', function () {
        const formRow = $(this).closest('tr');
        const harga = parseInt(formRow.find('.form-harga').val());
        const qty = parseInt(formRow.find('.form-qty').val());
        const stok = parseInt(formRow.find('.form-stok').val());

        if (qty > stok) {
            alert('Jumlah melebihi stok yang tersedia!');
            return;
        }

        const total = harga * qty;

        const originalRow = formRow.prev();
        originalRow.find('td:eq(3)').text(harga);
        originalRow.find('td:eq(4)').text(qty);
        originalRow.find('td:eq(5)').text(total);

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
        const grandTotal = parseInt($('#grandTotal').val()) || 0;
        const cash = parseInt($('#cash').val()) || 0;
    
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
        const note = $('#note').val(); // Menangkap nilai note
    
        $('#salesTable tr').each(function () {
            const kodeBarang = $(this).find('td:eq(1)').text();
            const namaBarang = $(this).find('td:eq(2)').text();
            const hargaBarang = parseInt($(this).find('td:eq(3)').text());
            const jumlah = parseInt($(this).find('td:eq(4)').text());
            const subtotal = parseInt($(this).find('td:eq(5)').text());
    
            // Ambil id_barang dari data barang yang sesuai
            const idBarang = $(this).data('id');
    
            if (idBarang && jumlah > 0) {
                items.push({
                    id_barang: idBarang,
                    kode_barang: kodeBarang,        // Menambahkan kode barang
                    nama_barang: namaBarang,        // Menambahkan nama barang
                    harga_barang: hargaBarang,      // Menambahkan harga barang
                    jumlah: jumlah,
                    subtotal: subtotal,
                    note: note                      // Menambahkan note ke setiap item
                });
            }
        });
    
        return items;
    }
    
    $('.btn-selesai-pembayaran').click(function () {
        const items = getCartItems();
        const metodePembayaran = $('#paymentMethod').val();
        const totalHarga = parseInt($('#grandTotal').val()) || 0;
        const note = $('#note').val(); // Ambil nilai note
    
        if (items.length === 0) {
            alert('Keranjang kosong!');
            return;
        }
    
        $.ajax({
            url: '/transaksi/store',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                metode_pembayaran: metodePembayaran,
                total_harga: totalHarga,
                items: items,
                note: note // Kirim note ke server
            },
            success: function (response) {
                // Tampilkan alert sukses, lalu setelah user tekan OK, tutup modal
                alert(response.message || 'Transaksi berhasil disimpan.');
    
                // Tutup modal setelah alert ditutup
                const modal = bootstrap.Modal.getInstance(document.getElementById('menungguPembayaranModal'));
                modal.hide();
    
                // Reset semua menggunakan fungsi reset lengkap
                resetAllTransaction();
            },
    
            error: function (xhr) {
                console.log('Full error response:', xhr);
                alert(xhr.responseJSON?.message || 'Gagal menyimpan transaksi.');
            }
        });
    });

    // ==================== Tombol Cancel - Reset Semua ====================
    $(document).on('click', '.btn-cancel', function () {
        if (confirm('Yakin ingin membatalkan transaksi? Semua data akan dihapus.')) {
            resetAllTransaction();
            
            // Tampilkan pesan konfirmasi
            alert('Transaksi telah dibatalkan dan semua data direset.');
        }
    });

    // Inisialisasi total awal
    calculateTotals();
});