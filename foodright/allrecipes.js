
function toggleDropdown(id) {
    const currentDropdown = document.getElementById(id);
    const allDropdowns = document.querySelectorAll('.dropdown');

    allDropdowns.forEach(dropdown => {
        if (dropdown !== currentDropdown) {
            dropdown.style.display = 'none';
        }
    });

    if (currentDropdown.style.display === 'block') {
        currentDropdown.style.display = 'none';
    } else {
        currentDropdown.style.display = 'block';
    }
}

document.addEventListener('click', function(event) {
    if (!event.target.closest('.sort-item')) {
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.style.display = 'none';
        });
    }
});
