document.addEventListener('DOMContentLoaded', function () {
    const filterBtn = document.getElementById('filter-button');
    const resetBtn = document.getElementById('reset-button');

    function formatTanggalIndo(dateStr) {
        const options = { year: 'numeric', month: 'long', day: '2-digit' };
        return new Date(dateStr).toLocaleDateString('id-ID', options);
    }

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
                    <td>${formatTanggalIndo(item.tanggal_dari)} s.d ${formatTanggalIndo(item.tanggal_sampai)}</td>
                    <td>
                        <a href="/prediksi/${item.id_prediksi}" class="btn btn-sm btn-primary">Detail</a>
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
