document.addEventListener("DOMContentLoaded", function () {
    const filterButton = document.getElementById("btn-filter");
    const searchBar = document.getElementById("search-bar");

    // Toggle filter lanjutan
    document.getElementById('toggleFilter').addEventListener('click', () => {
        const adv = document.getElementById('advanced-filters');
        adv.style.display = adv.style.display === 'none' ? 'block' : 'none';
    });

    // Tombol filter klik
    document.getElementById('filter-button').addEventListener("click", () => {
        const params = new URLSearchParams();

        const startDate = document.getElementById("start-date").value;
        const endDate = document.getElementById("end-date").value;
        const keyword = document.getElementById("search-bar").value;
        const metode = document.getElementById("metode-pembayaran")?.value;
        const hargaMin = document.getElementById("harga-min")?.value;
        const hargaMax = document.getElementById("harga-max")?.value;

        if (startDate) params.append("start_date", startDate);
        if (endDate) params.append("end_date", endDate);
        if (keyword) params.append("keyword", keyword);
        if (metode) params.append("metode", metode);
        if (hargaMin) params.append("harga_min", hargaMin);
        if (hargaMax) params.append("harga_max", hargaMax);

        window.location.href = `/laporan?${params.toString()}`;
    });

    // Modal detail transaksi
    document.querySelectorAll(".btn-detail").forEach((button) => {
        button.addEventListener("click", function (e) {
            e.preventDefault();
            const id = this.dataset.id;

            fetch(`/transaksi/detail/${id}`)
                .then((res) => res.json())
                .then((data) => {
                    const detailBody = document.getElementById("detail-body");
                    let html = `
                        <p><strong>ID Transaksi:</strong> ${data.id_transaksi}</p>
                        <p><strong>Tanggal:</strong> ${new Date(data.tanggal_waktu).toLocaleString()}</p>
                        <p><strong>Metode Pembayaran:</strong> ${data.metode_pembayaran}</p>
                        <p><strong>Total:</strong> Rp${parseInt(data.total_harga).toLocaleString("id-ID")}</p>
                        <p><strong>Catatan:</strong> ${data.note ? data.note : '-'}</p>
                        <hr>
                        <h4>Barang:</h4>
                        <table class="table-detail">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    data.detail_transaksi.forEach((item) => {
                        html += `
                            <tr>
                                <td>${item.barang?.nama_barang ?? '-'}</td>
                                <td>${item.jumlah}</td>
                                <td>Rp${parseInt(item.subtotal).toLocaleString("id-ID")}</td>
                            </tr>
                        `;
                    });

                    html += `</tbody></table>`;
                    detailBody.innerHTML = html;

                    // Tampilkan modal
                    const modal = document.getElementById("modal-detail");
                    modal.style.display = "flex";
                    document.body.style.overflow = "hidden";
                })
                .catch((err) => {
                    alert("Gagal mengambil detail transaksi");
                    console.error(err);
                });
        });
    });

    // Validasi tanggal
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');

    // Saat start date berubah, set minimal tanggal untuk end date
    startDateInput.addEventListener('change', () => {
        if (startDateInput.value) {
            endDateInput.min = startDateInput.value;
            // Jika end date lebih kecil dari start date, reset end date
            if (endDateInput.value && endDateInput.value < startDateInput.value) {
                endDateInput.value = startDateInput.value;
            }
        } else {
            endDateInput.min = '';
        }
    });

    // Saat end date berubah, set maksimal tanggal untuk start date
    endDateInput.addEventListener('change', () => {
        if (endDateInput.value) {
            startDateInput.max = endDateInput.value;
            // Jika start date lebih besar dari end date, reset start date
            if (startDateInput.value && startDateInput.value > endDateInput.value) {
                startDateInput.value = endDateInput.value;
            }
        } else {
            startDateInput.max = '';
        }
    });

    // Inisialisasi minimal dan maksimal saat halaman dimuat (jika sudah ada nilai)
    if (startDateInput.value) {
        endDateInput.min = startDateInput.value;
    }
    if (endDateInput.value) {
        startDateInput.max = endDateInput.value;
    }

    // Tutup modal dengan tombol
    document.getElementById("close-modal").addEventListener("click", function () {
        closeModal();
    });

    // Tutup modal jika klik di luar konten
    document.getElementById("modal-detail").addEventListener("click", function (e) {
        if (e.target === this) {
            closeModal();
        }
    });

    function closeModal() {
        document.getElementById("modal-detail").style.display = "none";
        document.body.style.overflow = "auto";
    }

    // Tombol export - Updated untuk menangani loading state
    document.getElementById('export-button').addEventListener("click", () => {
        const format = document.getElementById("export-format").value;
        const button = document.getElementById('export-button');
        
        // Tampilkan loading state
        const originalText = button.innerHTML;
        button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Mengexport...`;
        button.disabled = true;
        
        // Set timeout untuk reset button setelah export dimulai
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 3000);
        
        exportFile(format);
    });

    // Reset button
    document.getElementById('reset-button').addEventListener('click', () => {
        // Kosongkan semua filter
        document.getElementById("start-date").value = '';
        document.getElementById("end-date").value = '';
        document.getElementById("search-bar").value = '';
        const metodeSelect = document.getElementById("metode-pembayaran");
        if(metodeSelect) metodeSelect.value = '';
        const hargaMin = document.getElementById("harga-min");
        if(hargaMin) hargaMin.value = '';
        const hargaMax = document.getElementById("harga-max");
        if(hargaMax) hargaMax.value = '';

        // Reset validasi tanggal
        startDateInput.min = '';
        startDateInput.max = '';
        endDateInput.min = '';
        endDateInput.max = '';

        // Sembunyikan filter lanjutan (optional)
        const adv = document.getElementById('advanced-filters');
        if(adv) adv.style.display = 'none';

        // Redirect ke halaman laporan tanpa query
        window.location.href = '/laporan';
    });
});

// Fungsi export file - Updated dengan parameter yang benar
function exportFile(type) {
    const params = new URLSearchParams();

    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;
    const keyword = document.getElementById("search-bar").value;
    const metode = document.getElementById("metode-pembayaran")?.value;
    const hargaMin = document.getElementById("harga-min")?.value;
    const hargaMax = document.getElementById("harga-max")?.value;

    // Gunakan parameter yang sesuai dengan controller
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    if (keyword) params.append('keyword', keyword);
    if (metode) params.append('metode', metode);
    if (hargaMin) params.append('harga_min', hargaMin);
    if (hargaMax) params.append('harga_max', hargaMax);

    let url = '';
    if (type === 'pdf') {
        url = `/laporan/export/pdf?${params.toString()}`;
    } else if (type === 'excel') {
        url = `/laporan/export/excel?${params.toString()}`;
    }

    if (url) {
        // Buat link sementara untuk download
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Tampilkan notifikasi sukses
        showNotification('Export berhasil! File akan diunduh secara otomatis.', 'success');
    } else {
        showNotification('Format export tidak valid!', 'error');
    }
}

// Fungsi untuk menampilkan notifikasi
function showNotification(message, type = 'info') {
    // Buat elemen notifikasi
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        max-width: 300px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    `;
    
    // Set warna berdasarkan type
    switch(type) {
        case 'success':
            notification.style.backgroundColor = '#28a745';
            break;
        case 'error':
            notification.style.backgroundColor = '#dc3545';
            break;
        case 'warning':
            notification.style.backgroundColor = '#ffc107';
            notification.style.color = '#000';
            break;
        default:
            notification.style.backgroundColor = '#17a2b8';
    }
    
    notification.textContent = message;
    
    // Tambahkan ke body
    document.body.appendChild(notification);
    
    // Hapus setelah 3 detik
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}