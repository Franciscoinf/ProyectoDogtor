<nav class="main-nav">
    <div class="nav-container">
        <div class="nav-brand">
            <a href="dashboard.php">🐶 Dogtor</a>
        </div>
        
        <ul class="nav-menu">
            <li>
                <a href="citas.php" class="<?= basename($_SERVER['PHP_SELF']) === 'citas.php' ? 'active' : '' ?>" aria-current="<?= basename($_SERVER['PHP_SELF']) === 'citas.php' ? 'page' : '' ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Citas</span>
                </a>
            </li>
            <li>
                <a href="guarderia.php" class="<?= basename($_SERVER['PHP_SELF']) === 'guarderia.php' ? 'active' : '' ?>">
                    <i class="fas fa-hotel"></i>
                    <span>Guardería</span>
                </a>
            </li>
            <li>
                <a href="seguros.php" class="<?= basename($_SERVER['PHP_SELF']) === 'seguros.php' ? 'active' : '' ?>">
                    <i class="fas fa-shield-alt"></i>
                    <span>Seguros</span>
                </a>
            </li>
            <li>
                <a href="admin_mascota.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_mascota.php' ? 'active' : '' ?>">
                    <i class="fas fa-paw"></i>
                    <span>Mascotas</span>
                </a>
            </li>
        </ul>

        <div class="nav-actions">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>
</nav>