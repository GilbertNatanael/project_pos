document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById('modalDetail');
    const modalBackdrop = modal.querySelector('.modal-backdrop');
    
    modal.style.display = 'none';
    modal.classList.add('hidden');
    modal.classList.remove('show');

    // Event: Close button
    document.getElementById('closeModal').addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        hideModal();
    });

    modalBackdrop.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        hideModal();
    });

    modal.querySelector('.modal-content').addEventListener('click', (e) => {
        e.stopPropagation();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            hideModal();
        }
    });

    // Handle pagination links dengan AJAX
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('page-link')) {
            e.preventDefault();
            const url = e.target.href;
            fetchPembelianFromUrl(url);
        }
    });

    // Handle form filter dengan AJAX
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        const url = this.action + '?' + params.toString();
        
        fetchPembelianFromUrl(url);
    });

    // Handle reset filter
    document.getElementById('resetFilter').addEventListener('click', function() {
        // Reset form inputs
        document.getElementById('nama_barang').value = '';
        document.getElementById('kategori_id').value = '';
        document.getElementById('tanggal_dari').value = '';
        document.getElementById('tanggal_sampai').value = '';
        
        // Fetch data tanpa filter
        const baseUrl = document.getElementById('filterForm').action;
        fetchPembelianFromUrl(baseUrl);
    });

    function hideModal() {
        modal.style.display = 'none';
        modal.classList.add('hidden');
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        enablePageInteraction(true);
    }

    function showModal() {
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        enablePageInteraction(false);
    }

    function enablePageInteraction(enable) {
        const interactiveElements = document.querySelectorAll(
            'a:not(#modalDetail a), button:not(#modalDetail button), input:not(#modalDetail input), select:not(#modalDetail select), textarea:not(#modalDetail textarea), [tabindex]:not(#modalDetail [tabindex])'
        );
        
        interactiveElements.forEach(element => {
            if (enable) {
                element.removeAttribute('tabindex');
                element.style.pointerEvents = '';
                if (element.hasAttribute('data-original-tabindex')) {
                    element.setAttribute('tabindex', element.getAttribute('data-original-tabindex'));
                    element.removeAttribute('data-original-tabindex');
                }
            } else {
                if (element.hasAttribute('tabindex')) {
                    element.setAttribute('data-original-tabindex', element.getAttribute('tabindex'));
                }
                element.setAttribute('tabindex', '-1');
                element.style.pointerEvents = 'none';
            }
        });
    }
    window.showModalHelper = showModal;
    window.hideModalHelper = hideModal;
});

// Fungsi untuk fetch data dari URL pagination atau filter
function fetchPembelianFromUrl(url) {
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Parse HTML response dan ambil bagian tabel + pagination
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Update tabel
        const newTbody = doc.querySelector('tbody');
        const currentTbody = document.querySelector('tbody');
        if (newTbody && currentTbody) {
            currentTbody.innerHTML = newTbody.innerHTML;
        }
        
        // Update pagination
        const newPagination = doc.querySelector('.pagination');
        const currentPagination = document.querySelector('.pagination');
        if (newPagination && currentPagination) {
            currentPagination.innerHTML = newPagination.innerHTML;
        }
        
        // Update URL browser tanpa reload
        window.history.pushState({}, '', url);
    })
    .catch(error => {
        console.error("Error fetching data:", error);
        showAlert('Terjadi kesalahan saat memuat data.', 'error');
    });
}

// Fungsi fetchPembelian hanya untuk refresh data di halaman yang sama
function fetchPembelian() {
    const currentUrl = window.location.href;
    fetchPembelianFromUrl(currentUrl);
}

function renderTable(pembelians) {
    const tbody = document.querySelector("tbody");
    tbody.innerHTML = '';

    if (pembelians.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center px-4 py-2">Belum ada data pembelian.</td></tr>`;
        return;
    }

    pembelians.forEach(p => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="px-4 py-2">${p.id}</td>
            <td class="px-4 py-2">${p.tanggal}</td>
            <td class="px-4 py-2">${p.total_item}</td>
            <td class="px-4 py-2">Rp ${p.total_harga}</td>
            <td class="px-4 py-2">
                <button class="text-blue-600 hover:underline" onclick="showDetail(${p.id})">Detail</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function showDetail(idPembelian) {
    fetch(`/pembelian/${idPembelian}/detail`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const detailBody = document.getElementById('detailBody');
        detailBody.innerHTML = '';

        if (!data || data.length === 0) {
            detailBody.innerHTML = `<tr><td colspan="6" class="text-center py-4">Tidak ada detail pembelian.</td></tr>`;
        } else {
            data.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.nama_barang}</td>
                    <td>${item.merek}</td>
                    <td>${item.kategori}</td>
                    <td>${item.jumlah}</td>
                    <td>${item.satuan}</td>
                    <td>Rp ${Number(item.subtotal).toLocaleString('id-ID')}</td>
                `;
                detailBody.appendChild(tr);
            });
        }

        // Show modal using helper function
        if (window.showModalHelper) {
            window.showModalHelper();
        }
    })
    .catch(error => {
        console.error("Error fetching detail:", error);
        showAlert('Gagal memuat detail pembelian.', 'error');
    });
}

function showAlert(message, type) {
    const alertBox = document.getElementById(`alert-${type}`);
    if (alertBox) {
        alertBox.textContent = message;
        alertBox.style.display = "block";
        setTimeout(() => alertBox.style.display = "none", 3000);
    }
}

window.showDetail = showDetail;

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const resetButton = document.getElementById('resetFilter');
    const filterForm = document.getElementById('filterForm');
    
    // Reset filter function
    resetButton.addEventListener('click', function() {
        // Clear all form inputs
        const inputs = filterForm.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.type === 'text' || input.type === 'date') {
                input.value = '';
            } else if (input.tagName === 'SELECT') {
                input.selectedIndex = 0;
            }
        });
        
        // Submit form to reload without filters
        filterForm.submit();
    });
    
    // Auto-submit on select change (optional)
    const selectElements = filterForm.querySelectorAll('select');
    selectElements.forEach(select => {
        select.addEventListener('change', function() {
            // Optional: Auto-submit when category changes
            // filterForm.submit();
        });
    });
    
    // Enter key submit for text inputs
    const textInputs = filterForm.querySelectorAll('input[type="text"]');
    textInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterForm.submit();
            }
        });
    });
});