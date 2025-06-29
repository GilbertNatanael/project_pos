document.addEventListener('DOMContentLoaded', function () {
    const filterBtn = document.getElementById('filter-button');
    const resetBtn = document.getElementById('reset-button');

function formatBulanIndo(monthStr) {
    // Validasi input
    if (!monthStr) return '-';
    
    // Format YYYY-MM menjadi "Bulan Tahun"
    const [year, month] = monthStr.split('-');
    if (year && month) {
        const date = new Date(year, month - 1);
        const options = { year: 'numeric', month: 'long' };
        return date.toLocaleDateString('id-ID', options);
    }
    
    return monthStr;
}

function formatTanggalIndo(dateStr) {
    if (!dateStr) return '-';
    
    const date = new Date(dateStr);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return date.toLocaleDateString('id-ID', options);
}

// Fungsi untuk menghapus prediksi
function deletePrediksi(id) {
    if (confirm(`Apakah Anda yakin ingin menghapus prediksi ${id}? Data ini tidak dapat dikembalikan!`)) {
        fetch('/cek-prediksi/delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Prediksi berhasil dihapus!');
                // Refresh data tabel
                fetchPrediksi();
            } else {
                alert('Error: ' + (data.error || 'Gagal menghapus prediksi'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus prediksi');
        });
    }
}

// Buat deletePrediksi menjadi global agar bisa diakses dari onclick
window.deletePrediksi = deletePrediksi;

    function fetchPrediksi() {
    const filterTanggalStart = document.getElementById('filter-tanggal-start').value;
    const filterTanggalEnd = document.getElementById('filter-tanggal-end').value;
    const periodeStart = document.getElementById('periode-start').value;
    const periodeEnd = document.getElementById('periode-end').value;
    const search = document.getElementById('search-bar').value;

    const params = new URLSearchParams();
    if (filterTanggalStart) params.append('tanggal_start', filterTanggalStart);
    if (filterTanggalEnd) params.append('tanggal_end', filterTanggalEnd);
    if (periodeStart) params.append('periode_start', periodeStart);
    if (periodeEnd) params.append('periode_end', periodeEnd);
    if (search) params.append('search', search);

    fetch(`/cek-prediksi/data?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('.laporan-table tbody');
            tbody.innerHTML = '';

            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center">Tidak ada data ditemukan.</td></tr>`;
                return;
            }

            data.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${'PRD-' + item.id_prediksi.toString().padStart(4, '0')}</td>
                    <td>${formatTanggalIndo(item.tanggal)}</td>
                    <td>${item.jumlah_item}</td>
                    <td>${formatBulanIndo(item.bulan_dari)} s.d ${formatBulanIndo(item.bulan_sampai)}</td>
                    <td>
                        <a href="/prediksi/${item.id_prediksi}" class="btn btn-sm btn-primary me-1">Detail</a>
                        <button onclick="deletePrediksi('PRD-${item.id_prediksi.toString().padStart(4, '0')}')" 
                                class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
}

    filterBtn.addEventListener('click', fetchPrediksi);

document.getElementById('reset-button').addEventListener('click', () => {
    document.getElementById('filter-tanggal-start').value = '';
    document.getElementById('filter-tanggal-end').value = '';
    document.getElementById('periode-start').value = '';
    document.getElementById('periode-end').value = '';
    document.getElementById('search-bar').value = '';
    fetchPrediksi();
});

    // Panggil fetch awal saat page load
    fetchPrediksi();
});