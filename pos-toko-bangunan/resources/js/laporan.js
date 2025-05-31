document.addEventListener("DOMContentLoaded", function () {
    const filterButton = document.getElementById("btn-filter");
    const searchBar = document.getElementById("search-bar");

    filterButton.addEventListener("click", () => {
        const startDate = document.getElementById("start-date").value;
        const endDate = document.getElementById("end-date").value;
        const keyword = searchBar.value;

        console.log("Filter diklik:", { startDate, endDate, keyword });
        // Di sinilah nanti fetch ke backend akan dilakukan
    });

    // Export buttons
    document.getElementById("export-pdf").addEventListener("click", () => {
        console.log("Export PDF diklik");
    });

    document.getElementById("export-excel").addEventListener("click", () => {
        console.log("Export Excel diklik");
    });

    // Detail modal
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

                    // Nonaktifkan scroll di halaman utama
                    document.body.style.overflow = "hidden";
                })
                .catch((err) => {
                    alert("Gagal mengambil detail transaksi");
                    console.error(err);
                });
        });
    });

    // Tutup modal dengan tombol
    document.getElementById("close-modal").addEventListener("click", function () {
        closeModal();
    });

    // Tutup modal dengan klik luar
    document.getElementById("modal-detail").addEventListener("click", function (e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Fungsi untuk menutup modal dan mengembalikan scroll
    function closeModal() {
        document.getElementById("modal-detail").style.display = "none";
        document.body.style.overflow = "auto";
    }
});
