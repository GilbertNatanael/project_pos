document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.sidebar-menu .dropdown > a');
    
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); 
            const parent = this.parentElement;
            parent.classList.toggle('active');
        });
    });


    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    
    menuItems.forEach(item => {
        if (item.parentElement.classList.contains('dropdown')) {
            return;
        }
        
        const href = item.getAttribute('href');
        if (href && currentPath.includes(href.replace(/^https?:\/\/[^\/]+/, ''))) {
            item.classList.add('active');

            const dropdownParent = item.closest('.dropdown');
            if (dropdownParent) {
                dropdownParent.classList.add('active');
            }
        }
    });

    document.querySelectorAll('.sidebar-menu .dropdown-menu a').forEach(item => {
        const href = item.getAttribute('href');
        if (href && currentPath.includes(href)) {
            item.classList.add('active');
            const dropdownParent = item.closest('.dropdown');
            if (dropdownParent) {
                dropdownParent.classList.add('active');
            }
        }
    });

    document.querySelectorAll('.sidebar .dropdown-menu').forEach(menu => {
        menu.classList.remove('dropdown-menu-end');
        menu.setAttribute('data-bs-popper', 'none');
    });
});