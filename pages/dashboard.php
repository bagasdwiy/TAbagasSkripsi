<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Query untuk Total Pendapatan (dari transaksi jual)
$query_pendapatan = "SELECT COALESCE(SUM(total), 0) as total_pendapatan 
                     FROM transaksi 
                     WHERE jenis = 'jual'";
$result_pendapatan = $db->query($query_pendapatan);
$total_pendapatan = $result_pendapatan->fetch_assoc()['total_pendapatan'];

// Query untuk Total Transaksi
$query_transaksi = "SELECT COUNT(*) as total_transaksi FROM transaksi";
$result_transaksi = $db->query($query_transaksi);
$total_transaksi = $result_transaksi->fetch_assoc()['total_transaksi'];

// Query untuk Total Barang
$query_barang = "SELECT COUNT(*) as total_barang FROM barang";
$result_barang = $db->query($query_barang);
$total_barang = $result_barang->fetch_assoc()['total_barang'];

// Query untuk Transaksi Hari Ini
$query_transaksi_hari_ini = "SELECT COUNT(*) as total 
                            FROM transaksi 
                            WHERE DATE(tanggal) = CURDATE()";
$result_transaksi_hari_ini = $db->query($query_transaksi_hari_ini);
$transaksi_hari_ini = $result_transaksi_hari_ini->fetch_assoc()['total'];

// Query untuk Stok Menipis (misalnya stok < 10)
$query_stok_menipis = "SELECT nama_barang, stok 
                       FROM barang 
                       WHERE stok < 10 
                       ORDER BY stok ASC 
                       LIMIT 5";
$result_stok_menipis = $db->query($query_stok_menipis);
?>
<?php Layout::header('Dashboard'); ?>
<!-- HTML dan JavaScript untuk menampilkan data -->
<div class="container-fluid">
    <!-- Cards -->
    <div class="row">
        <!-- Total Pendapatan Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                TOTAL PENDAPATAN</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rp <?= number_format($total_pendapatan, 0, ',', '.') ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Transaksi Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                TOTAL TRANSAKSI</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $total_transaksi ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Barang Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                TOTAL BARANG</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $total_barang ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaksi Hari Ini Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                TRANSAKSI HARI INI</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $transaksi_hari_ini ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stok Menipis -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Stok Menipis</h6>
                </div>
                <div class="card-body">
                    <?php if ($result_stok_menipis->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Barang</th>
                                        <th>Stok</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result_stok_menipis->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                                            <td><?= $row['stok'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Tidak ada barang dengan stok menipis</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php Layout::footer(); ?>