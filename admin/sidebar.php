<nav class="sidebar">
    <div class="sidebar-header">
        <h2>
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
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'active' : ''; ?>">Usuarios</a></li>
        <li><a href="ninos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'ninos.php' ? 'active' : ''; ?>">Niños</a></li>
        <li><a href="pagos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pagos.php' ? 'active' : ''; ?>">Pagos</a></li>
    </ul>
    <div class="sidebar-footer">
        <button id="darkModeToggle" class="dark-mode-toggle" title="Modo Oscuro">
            <span class="dark-mode-icon">🌙</span>
            <span class="dark-mode-text">Modo Oscuro</span>
        </button>
    </div>
</nav>










