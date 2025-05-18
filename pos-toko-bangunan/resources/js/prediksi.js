// Menangani modal dan interaksi tombol
document.addEventListener('DOMContentLoaded', function () {
    const startPredictionButton = document.getElementById('start-prediction');
    const modalPrediksi = document.getElementById('modalPrediksi');
    const cancelPrediksiButton = document.getElementById('cancelPrediksi');
    const confirmPrediksiButton = document.getElementById('confirmPrediksi');
    const jumlahBulanInput = document.getElementById('jumlahBulan');

    // Tampilkan modal ketika tombol "Mulai Prediksi" diklik
    startPredictionButton.addEventListener('click', function () {
        modalPrediksi.style.display = 'flex';
    });

    // Menutup modal jika tombol Batal diklik
    cancelPrediksiButton.addEventListener('click', function () {
        modalPrediksi.style.display = 'none';
    });

    // Konfirmasi prediksi
    confirmPrediksiButton.addEventListener('click', function () {
        const jumlahBulan = jumlahBulanInput.value;
        if (jumlahBulan) {
            // Melakukan prediksi berdasarkan jumlah bulan yang dipilih
            alert(`Prediksi untuk ${jumlahBulan} bulan dimulai.`);
            modalPrediksi.style.display = 'none';
        } else {
            alert('Masukkan jumlah bulan yang valid.');
        }
    });
});
