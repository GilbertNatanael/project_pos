document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('.table tbody tr');
    
    rows.forEach(row => {
        row.addEventListener('click', function() {
            row.style.backgroundColor = '#d1e7dd'; // Highlight the row on click
            setTimeout(() => {
                row.style.backgroundColor = ''; // Reset highlight after a delay
            }, 1500);
        });
    });
});
