// Dark Mode Toggle
(function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const html = document.documentElement;
    
    // Verificar si hay preferencia guardada
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    // Aplicar modo oscuro al cargar
    if (isDarkMode) {
        html.classList.add('dark-mode');
        updateDarkModeToggle(true);
    }
    
    // Función para actualizar el botón
    function updateDarkModeToggle(isDark) {
        const icon = darkModeToggle.querySelector('.dark-mode-icon');
        const text = darkModeToggle.querySelector('.dark-mode-text');
        if (isDark) {
            icon.textContent = '☀️';
            text.textContent = 'Modo Claro';
        } else {
            icon.textContent = '🌙';
            text.textContent = 'Modo Oscuro';
        }
    }
    
    // Event listener para el botón
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            const isDark = html.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', isDark);
            updateDarkModeToggle(isDark);
        });
    }
})();






