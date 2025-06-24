// Bagian yang perlu diganti dalam attachEditListeners()
// Ganti function attachEditListeners() di paste-2.txt
// Ganti function attachEditListeners() di paste-2.txt
function attachEditListeners() {
    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const row = this.closest('tr');
            const id = this.dataset.id;
            const kode = this.dataset.kode;
            const nama = this.dataset.nama;
            const harga = this.dataset.harga;
            const jumlah = this.dataset.jumlah;
            const satuan = this.dataset.satuan || '';
            const merk = this.dataset.merk || '';
            const kategori_id = this.dataset.kategori_id || '';

            // Hide all rows first
            document.querySelectorAll('tr[data-row]').forEach(tr => tr.style.display = '');
            document.querySelectorAll('tr[data-form]').forEach(tr => tr.remove());

            // Generate kategori options
            let kategoriOptions = '<option value="">-- Pilih Kategori --</option>';
            if (window.kategoriList) {
                window.kategoriList.forEach(kategori => {
                    const selected = kategori.id == kategori_id ? 'selected' : '';
                    kategoriOptions += `<option value="${kategori.id}" ${selected}>${kategori.nama_kategori}</option>`;
                });
            }

            // Create new form row
            const formRow = document.createElement('tr');
            formRow.setAttribute('data-form', id);
            formRow.innerHTML = `
                <td colspan="8">
                    <form method="POST" action="/barang/${id}" class="inline-edit-form flex flex-col gap-2">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                        <input type="hidden" name="_method" value="PUT">
                        <div class="grid grid-cols-3 gap-4 mb-2">
                            <input type="text" name="kode_barang" value="${kode}" placeholder="Kode Barang" class="border p-2 rounded" required />
                            <input type="text" name="nama_barang" value="${nama}" placeholder="Nama Barang" class="border p-2 rounded" required />
                            <input type="text" name="merek" value="${merk}" placeholder="Merk Barang" class="border p-2 rounded" />
                        </div>
                        <div class="grid grid-cols-4 gap-4 mb-2">
                            <select name="kategori_id" class="border p-2 rounded" required>
                                ${kategoriOptions}
                            </select>
                            <select name="satuan_barang" class="border p-2 rounded" required>
                                <option value="">-- Pilih Satuan --</option>
                                <option value="pcs" ${satuan === 'pcs' ? 'selected' : ''}>pcs</option>
                                <option value="kg" ${satuan === 'kg' ? 'selected' : ''}>kg</option>
                                <option value="liter" ${satuan === 'liter' ? 'selected' : ''}>liter</option>
                                <option value="meter" ${satuan === 'meter' ? 'selected' : ''}>meter</option>
                                <option value="lusin" ${satuan === 'lusin' ? 'selected' : ''}>lusin</option>
                            </select>
                            <input type="number" name="jumlah_barang" value="${jumlah}" placeholder="Jumlah" class="border p-2 rounded" required />
                            <input type="number" name="harga_barang" value="${harga}" placeholder="Harga" class="border p-2 rounded" required />
                        </div>
                        <div class="mt-2 flex gap-2">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
                            <button type="button" class="bg-gray-400 text-white px-4 py-2 rounded batal-button">Batal</button>
                        </div>
                    </form>
                </td>
            `;
            row.style.display = 'none';
            row.insertAdjacentElement('afterend', formRow);

            // Handle cancel
            formRow.querySelector('.batal-button').addEventListener('click', () => {
                formRow.remove();
                row.style.display = '';
            });
        });
    });
}

// Initialize edit listeners when page loads
document.addEventListener('DOMContentLoaded', function() {
    attachEditListeners();
});
// Function to hide alert after 5 seconds
function hideAlert() {
    setTimeout(function() {
        var successAlert = document.getElementById('alert-success');
        var errorAlert = document.getElementById('alert-error');
        if (successAlert) {
            successAlert.style.display = 'none';
        }
        if (errorAlert) {
            errorAlert.style.display = 'none';
        }
    }, 5000);
}

// Run the hideAlert function if there's any alert
window.onload = function() {
    hideAlert();
};

// Event delegation untuk klik link pagination - Perbaikan Sederhana
document.addEventListener('click', function (e) {
    if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
        e.preventDefault();
        const url = e.target.getAttribute('href');

        // Simpan posisi scroll
        const scrollPosition = window.scrollY;
        
        // Tambahkan loading state
        const tableContainer = document.querySelector('.bg-white.shadow.rounded.overflow-x-auto');
        tableContainer.classList.add('table-loading');
        document.body.classList.add('loading');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.html) {
                // Buat temporary div untuk parsing HTML baru
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html;
                
                // Ambil bagian tabel baru
                const newTableContainer = tempDiv.querySelector('.bg-white.shadow.rounded.overflow-x-auto');
                const newPaginationContainer = tempDiv.querySelector('.pagination')?.closest('div');
                
                // Replace table container
                if (newTableContainer) {
                    tableContainer.innerHTML = newTableContainer.innerHTML;
                }
                
                // Replace atau update pagination
                const currentPaginationContainer = document.querySelector('.pagination')?.closest('div');
                if (newPaginationContainer && currentPaginationContainer) {
                    currentPaginationContainer.innerHTML = newPaginationContainer.innerHTML;
                } else if (newPaginationContainer && !currentPaginationContainer) {
                    // Tambahkan pagination setelah table container
                    tableContainer.insertAdjacentHTML('afterend', newPaginationContainer.outerHTML);
                }
                
                // Re-attach event listeners
                attachEditListeners();
                
                // Remove loading state
                tableContainer.classList.remove('table-loading');
                document.body.classList.remove('loading');
                
                // Restore scroll position
                setTimeout(() => {
                    window.scrollTo({
                        top: scrollPosition,
                        behavior: 'smooth'
                    });
                }, 50);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Remove loading state
            tableContainer.classList.remove('table-loading');
            document.body.classList.remove('loading');
            // Fallback ke redirect normal jika AJAX gagal
            window.location.href = url;
        });
    }
});