document.addEventListener('DOMContentLoaded', function () {
    const keranjang = [];
    document.getElementById('daftarBarang').addEventListener('click', function (e) {
    if (e.target.classList.contains('btn-tambah-barang')) {
        const row = e.target.closest('tr');
        const id = row.dataset.id;
        const nama = row.dataset.nama;
        const harga = parseInt(row.dataset.harga);
        const satuan = row.dataset.satuan;

        let existing = keranjang.find(item => item.id === id);
        if (existing) {
            existing.qty += 1;
        } else {
            keranjang.push({ id, nama, harga: 0, satuan, qty: 1 });
        }

        renderKeranjang();
    }
});

    const tbodyKeranjang = document.getElementById('keranjangPembelian');
    const totalHargaEl = document.getElementById('totalHarga');



    function renderKeranjang() {
        tbodyKeranjang.innerHTML = '';
        let total = 0;

        keranjang.forEach((item, index) => {
            const subtotal = item.harga * item.qty;
            total += subtotal;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.nama}</td>
                <td><input type="number" class="form-control form-control-sm qty-input" data-index="${index}" value="${item.qty}" min="1" style="width: 70px;"></td>
                <td>${item.satuan}</td>
                <td><input type="number" class="form-control harga-input" data-index="${index}" value="${item.harga}" min="0" style="width: 120px;"></td>
                <td>Rp ${subtotal.toLocaleString()}</td>
                <td><button class="btn btn-sm btn-danger btn-hapus" data-index="${index}">&times;</button></td>
            `;

            tbodyKeranjang.appendChild(row);
        });

        totalHargaEl.textContent = `Rp ${total.toLocaleString()}`;
        attachEvents();
    }

function attachEvents() {
    // Update jumlah
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function () {
            const index = this.dataset.index;
            const newQty = parseInt(this.value);
            if (newQty > 0) {
                keranjang[index].qty = newQty;
                renderKeranjang();
            }
        });
    });

    // Update harga
    document.querySelectorAll('.harga-input').forEach(input => {
        input.addEventListener('change', function () {
            const index = this.dataset.index;
            const newHarga = parseInt(this.value);
            if (newHarga >= 0) {
                keranjang[index].harga = newHarga;
                renderKeranjang();
            }
        });
    });

    // Hapus item
    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', function () {
            const index = this.dataset.index;
            keranjang.splice(index, 1);
            renderKeranjang();
        });
    });
}


    document.getElementById('btnResetPembelian').addEventListener('click', function () {
        keranjang.length = 0;
        renderKeranjang();
    });

    document.getElementById('btnSimpanPembelian').addEventListener('click', function () {
    if (keranjang.length === 0) {
        alert('Keranjang kosong!');
        return;
    }

    const data = {
        items: keranjang.map(item => ({
            id: item.id,
            nama: item.nama,
            qty: item.qty,
            harga: item.harga,
            satuan: item.satuan
        })),
        total: keranjang.reduce((sum, item) => sum + (item.harga * item.qty), 0)
    };

    fetch('/pembelian/simpan', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) throw new Error('Gagal menyimpan!');
        return response.json();
    })
    .then(data => {
        // Redirect ke halaman utama pembelian setelah sukses
        window.location.href = '/pembelian';
    })
    .catch(error => {
        alert('Terjadi kesalahan: ' + error.message);
    });
});



        // Filter pencarian barang
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        const rows = document.querySelectorAll('#daftarBarang tr');

        rows.forEach(row => {
            const namaBarang = row.dataset.nama.toLowerCase();
            const kodeBarang = row.children[0].textContent.toLowerCase(); // Kolom kode barang

            if (namaBarang.includes(keyword) || kodeBarang.includes(keyword)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });


});