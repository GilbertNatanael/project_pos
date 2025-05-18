document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown toggle
    const dropdowns = document.querySelectorAll('.sidebar-menu .dropdown > a');
    
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent Bootstrap conflicts
            const parent = this.parentElement;
            parent.classList.toggle('active');
        });
    });

    // Keep the current page's menu item active
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    
    menuItems.forEach(item => {
        // Skip dropdown parent links
        if (item.parentElement.classList.contains('dropdown')) {
            return;
        }
        
        const href = item.getAttribute('href');
        if (href && currentPath.includes(href.replace(/^https?:\/\/[^\/]+/, ''))) {
            item.classList.add('active');
            
            // If in dropdown, also activate parent dropdown
            const dropdownParent = item.closest('.dropdown');
            if (dropdownParent) {
                dropdownParent.classList.add('active');
            }
        }
    });
    
    // For dropdown items, ensure parent stays open when active
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

    // Prevent Bootstrap from controlling our sidebar dropdowns
    document.querySelectorAll('.sidebar .dropdown-menu').forEach(menu => {
        menu.classList.remove('dropdown-menu-end');
        menu.setAttribute('data-bs-popper', 'none');
    });
});