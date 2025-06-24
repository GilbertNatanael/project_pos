document.addEventListener('DOMContentLoaded', function () {
    const keranjang = [];
    let allRows = [];
    let filteredRows = [];
    let currentPage = 1;
    let itemsPerPage = 10;

    // Initialize pagination
    function initializePagination() {
        allRows = Array.from(document.querySelectorAll('#daftarBarang tr'));
        filteredRows = [...allRows];
        updatePagination();
    }

    // Update pagination display
    function updatePagination() {
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        // Hide all rows first
        allRows.forEach(row => row.style.display = 'none');

        // Show only rows for current page
        filteredRows.slice(startIndex, endIndex).forEach(row => {
            row.style.display = '';
        });

        // Update pagination info
        document.getElementById('currentItems').textContent = filteredRows.length;
        document.getElementById('paginationInfo').textContent = 
            `Halaman ${currentPage} dari ${totalPages || 1}`;

        // Update navigation buttons
        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === totalPages || totalPages === 0;

        // Generate page numbers
        generatePageNumbers(totalPages);
    }

    // Generate pagination numbers
    function generatePageNumbers(totalPages) {
        const paginationNumbers = document.getElementById('paginationNumbers');
        paginationNumbers.innerHTML = '';

        if (totalPages <= 1) return;

        // Calculate range of pages to show
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        // Adjust range if near beginning or end
        if (currentPage <= 3) {
            endPage = Math.min(5, totalPages);
        }
        if (currentPage > totalPages - 3) {
            startPage = Math.max(1, totalPages - 4);
        }

        // Add first page and ellipsis if needed
        if (startPage > 1) {
            addPageButton(1);
            if (startPage > 2) {
                paginationNumbers.appendChild(createEllipsis());
            }
        }

        // Add page numbers
        for (let i = startPage; i <= endPage; i++) {
            addPageButton(i);
        }

        // Add ellipsis and last page if needed
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationNumbers.appendChild(createEllipsis());
            }
            addPageButton(totalPages);
        }
    }

    // Add page button
    function addPageButton(pageNum) {
        const button = document.createElement('button');
        button.className = `btn btn-sm ${pageNum === currentPage ? 'btn-primary' : 'btn-outline-primary'}`;
        button.textContent = pageNum;
        button.addEventListener('click', () => goToPage(pageNum));
        document.getElementById('paginationNumbers').appendChild(button);
    }

    // Create ellipsis element
    function createEllipsis() {
        const span = document.createElement('span');
        span.className = 'btn btn-sm btn-outline-secondary disabled';
        span.textContent = '...';
        return span;
    }

    // Go to specific page
    function goToPage(pageNum) {
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        if (pageNum >= 1 && pageNum <= totalPages) {
            currentPage = pageNum;
            updatePagination();
        }
    }

    // Filter and search functionality
    function applyFilters() {
        const searchKeyword = document.getElementById('searchInput').value.toLowerCase();
        const selectedCategory = document.getElementById('filterKategori').value.toLowerCase();

        filteredRows = allRows.filter(row => {
            const namaBarang = row.dataset.nama.toLowerCase();
            const kodeBarang = row.children[0].textContent.toLowerCase();
            const kategoriBarang = row.dataset.kategori || '';

            const matchesSearch = namaBarang.includes(searchKeyword) || kodeBarang.includes(searchKeyword);
            const matchesCategory = !selectedCategory || kategoriBarang === selectedCategory;

            return matchesSearch && matchesCategory;
        });

        currentPage = 1; // Reset to first page when filtering
        updatePagination();
    }

    // Event listeners for pagination
    document.getElementById('prevPage').addEventListener('click', () => {
        if (currentPage > 1) {
            goToPage(currentPage - 1);
        }
    });

    document.getElementById('nextPage').addEventListener('click', () => {
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        if (currentPage < totalPages) {
            goToPage(currentPage + 1);
        }
    });

    // Items per page change
    document.getElementById('itemsPerPage').addEventListener('change', function() {
        itemsPerPage = parseInt(this.value);
        currentPage = 1;
        updatePagination();
    });

    // Search and filter event listeners
    document.getElementById('searchInput').addEventListener('keyup', applyFilters);
    document.getElementById('filterKategori').addEventListener('change', applyFilters);

    // Original cart functionality
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
            window.location.href = '/pembelian';
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
    });

    // Initialize pagination on page load
    initializePagination();
});