<div class="sidebar">
    <div class="logo">Toko Saya</div>
    
    <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-home"></i>Dashboard
    </a>
    
    <a href="kasir.php" <?= basename($_SERVER['PHP_SELF']) == 'kasir.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-cash-register"></i>Kasir
    </a>
    
    <a href="barang.php" <?= basename($_SERVER['PHP_SELF']) == 'barang.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-box"></i>Barang
    </a>
    
    <a href="supplier.php" <?= basename($_SERVER['PHP_SELF']) == 'supplier.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-truck"></i>Supplier
    </a>
    
    <a href="retur.php" <?= basename($_SERVER['PHP_SELF']) == 'retur.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-exchange-alt"></i>Retur
    </a>
    
    <a href="laporan.php" <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'class="active"' : '' ?>>
        <i class="fas fa-file-alt"></i>Laporan
    </a>
    
    <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i>Logout
    </a>
</div>
