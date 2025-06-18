// Menangani form submit
document.getElementById('createBarangForm').addEventListener('submit', function(event) {
    // Menampilkan konfirmasi
    var confirmSave = confirm("Apakah Anda yakin ingin menyimpan data barang?");
    
    // Jika user tidak menekan "OK", maka form tidak akan disubmit
    if (!confirmSave) {
        event.preventDefault(); // Mencegah submit form
    }
});

    