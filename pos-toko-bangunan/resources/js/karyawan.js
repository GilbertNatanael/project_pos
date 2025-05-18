document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const row = this.closest('tr');
            const id = this.dataset.id;
            const username = this.dataset.username;
            const role = this.dataset.role;

            // Hide all rows first
            document.querySelectorAll('tr[data-row]').forEach(tr => tr.style.display = '');
            document.querySelectorAll('tr[data-form]').forEach(tr => tr.remove());

            // Create new form row
            const formRow = document.createElement('tr');
            formRow.setAttribute('data-form', id);
            formRow.innerHTML = `
                <td colspan="3">
                    <form method="POST" action="/karyawan/${id}" class="inline-edit-form flex flex-col gap-2">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                        <input type="hidden" name="_method" value="PUT">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="username" value="${username}" class="border p-2 rounded" required />
                            <select name="role" class="border p-2 rounded" required>
                                <option value="owner" ${role === 'owner' ? 'selected' : ''}>Owner</option>
                                <option value="karyawan" ${role === 'karyawan' ? 'selected' : ''}>Karyawan</option>
                            </select>
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
});
