document.addEventListener('DOMContentLoaded', function () {
    attachEditListeners();
});

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

            // Hide all rows first
            document.querySelectorAll('tr[data-row]').forEach(tr => tr.style.display = '');
            document.querySelectorAll('tr[data-form]').forEach(tr => tr.remove());

            // Create new form row
            const formRow = document.createElement('tr');
            formRow.setAttribute('data-form', id);
                formRow.innerHTML = `
                    <td colspan="6">
                        <form method="POST" action="/barang/${id}" class="inline-edit-form flex flex-col gap-2">
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                            <input type="hidden" name="_method" value="PUT">
                            <div class="grid grid-cols-3 gap-4 mb-2">
                                <input type="text" name="kode_barang" value="${kode}" placeholder="Kode Barang" class="border p-2 rounded" required />
                                <input type="text" name="nama_barang" value="${nama}" placeholder="Nama Barang" class="border p-2 rounded" required />
                                <input type="text" name="satuan_barang" value="${satuan}" placeholder="Satuan (opsional)" class="border p-2 rounded" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <input type="number" name="harga_barang" value="${harga}" placeholder="Harga" class="border p-2 rounded" required />
                                <input type="number" name="jumlah_barang" value="${jumlah}" placeholder="Jumlah" class="border p-2 rounded" required />
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

// Event delegation untuk klik link pagination
document.addEventListener('click', function (e) {
    if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
        e.preventDefault();
        const url = e.target.getAttribute('href');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            // Replace entire content area
            document.querySelector('.container').innerHTML = data.html;
            
            // Re-attach event listeners
            attachEditListeners();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
});