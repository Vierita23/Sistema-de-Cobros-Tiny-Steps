// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    const menuOverlay = document.getElementById('menuOverlay');
    
    if (menuToggle && sidebar && menuOverlay) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            menuOverlay.classList.toggle('active');
        });
        
        menuOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            menuOverlay.classList.remove('active');
        });
        
        // Cerrar menú al hacer clic en un enlace (solo en móvil)
        if (window.innerWidth <= 768) {
            const menuLinks = sidebar.querySelectorAll('a');
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    menuOverlay.classList.remove('active');
                });
            });
        }
    }
    
    // Ajustar en resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            menuOverlay.classList.remove('active');
        }
    });
});


