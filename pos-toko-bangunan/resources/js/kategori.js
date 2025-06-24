document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const row = this.closest('tr');
            const id = this.dataset.id;
            const nama = this.dataset.nama_kategori;


            // Sembunyikan semua form yang sedang aktif
            document.querySelectorAll('tr[data-row]').forEach(tr => tr.style.display = '');
            document.querySelectorAll('tr[data-form]').forEach(tr => tr.remove());

            // Buat elemen baris form baru
            const formRow = document.createElement('tr');
            formRow.setAttribute('data-form', id);
            formRow.innerHTML = `
                <td colspan="2">
                    <form method="POST" action="/master/kategori/${id}/update" class="inline-edit-form flex flex-col gap-2">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="text" name="nama_kategori" value="${nama}" class="border p-2 rounded w-full" required />

                        <div class="mt-2 flex gap-2">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
                            <button type="button" class="bg-gray-400 text-white px-4 py-2 rounded batal-button">Batal</button>
                        </div>
                    </form>
                </td>
            `;

            row.style.display = 'none';
            row.insertAdjacentElement('afterend', formRow);

            // Event batal
            formRow.querySelector('.batal-button').addEventListener('click', () => {
                formRow.remove();
                row.style.display = '';
            });
        });
    });
});
