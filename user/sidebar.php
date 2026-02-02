<aside class="sidebar" id="sidebar" style="width: 250px; background: var(--white); box-shadow: 2px 0 10px rgba(0,0,0,0.1); padding: 20px 0; position: fixed; height: 100vh; left: 0; top: 0; z-index: 1000; overflow-y: auto;">
    <div style="padding: 0 20px 30px 20px; border-bottom: 2px solid var(--gray-light); margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 1.5em; color: var(--primary);">
            <span style="color: #FF00FF;">T</span>
            <span style="color: #00FF00;">i</span>
            <span style="color: #0000FF;">n</span>
            <span style="color: #FF0000;">y</span>
            <span style="color: #FFFF00;">S</span>
            <span style="color: #00FF00;">t</span>
            <span style="color: #FF0000;">e</span>
            <span style="color: #00BFFF;">p</span>
            <span style="color: #FF00FF;">s</span>
        </h2>
        <p style="margin: 5px 0 0 0; color: var(--gray-dark); font-size: 0.85em; font-weight: 600;">Centro de Desarrollo Infantil</p>
    </div>
    
    <nav style="padding: 0 10px;">
        <a href="dashboard.php" style="display: block; padding: 12px 20px; color: var(--dark); text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: all 0.3s ease; font-weight: 500;">
            🏠 Dashboard
        </a>
        <a href="subir_pago.php" style="display: block; padding: 12px 20px; color: var(--dark); text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: all 0.3s ease; font-weight: 500;">
            📤 Subir Pago
        </a>
        <a href="pagos.php" style="display: block; padding: 12px 20px; color: var(--dark); text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: all 0.3s ease; font-weight: 500;">
            💰 Mis Pagos
        </a>
        <a href="nino.php" style="display: block; padding: 12px 20px; color: var(--dark); text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: all 0.3s ease; font-weight: 500;">
            👶 Mis Niños
        </a>
    </nav>
    
    <div style="position: absolute; bottom: 20px; left: 0; right: 0; padding: 0 20px;">
        <div style="padding: 15px; background: var(--light); border-radius: 8px; text-align: center;">
            <p style="margin: 0 0 10px 0; font-size: 0.9em; color: var(--gray);"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            <a href="../logout.php" style="display: block; padding: 8px; background: var(--error); color: white; text-decoration: none; border-radius: 6px; font-size: 0.9em; font-weight: 600;">
            Cerrar Sesión
        </a>
        </div>
    </div>
</aside>

<style>
.sidebar a:hover {
    background: var(--light);
    color: var(--primary);
    transform: translateX(5px);
}

.sidebar a.active {
    background: var(--primary);
    color: white;
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    .main-content {
        margin-left: 0 !important;
    }
}
</style>
