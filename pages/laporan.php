<?php
require_once '../includes/init.php';
require_once '../classes/TransaksiReport.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Set default date range ke hari ini
$tgl_mulai = $_GET['start_date'] ?? date('Y-m-d');
$tgl_selesai = $_GET['end_date'] ?? date('Y-m-d');

// Jika ada request export
if (isset($_GET['export'])) {
    $report = new TransaksiReport();
    
    // Set date range jika ada
    if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
        $report->setDateRange($_GET['start_date'], $_GET['end_date']);
    }
    
    if ($_GET['export'] === 'pdf') {
        $report->exportPDF();
    } else if ($_GET['export'] === 'excel') {
        $report->exportExcel();
    }
    exit;
}

Layout::header('Laporan Transaksi'); 
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Laporan Transaksi</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form method="GET" class="form-inline">
                <div class="form-group mx-sm-3">
                    <label class="mr-2">Tanggal Awal</label>
                    <input type="date" name="start_date" class="form-control" 
                           value="<?= htmlspecialchars($tgl_mulai) ?>">
                </div>
                <div class="form-group mx-sm-3">
                    <label class="mr-2">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control"
                           value="<?= htmlspecialchars($tgl_selesai) ?>">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="?export=pdf&start_date=<?= urlencode($tgl_mulai) ?>&end_date=<?= urlencode($tgl_selesai) ?>" 
                   class="btn btn-danger mr-2">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
                <a href="?export=excel&start_date=<?= urlencode($tgl_mulai) ?>&end_date=<?= urlencode($tgl_selesai) ?>" 
                   class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>

            <?php
            // Query untuk mengambil data transaksi dengan nama pembeli
            $query = "SELECT t.*, p.nama_pembeli, p.no_telp 
                     FROM transaksi t 
                     LEFT JOIN pembeli p ON t.id_pembeli = p.id 
                     WHERE t.jenis = 'jual' 
                     AND DATE(t.tanggal) BETWEEN ? AND ?
                     ORDER BY t.tanggal DESC";

            $stmt = $db->prepare($query);
            $stmt->bind_param("ss", $tgl_mulai, $tgl_selesai);
            $stmt->execute();
            $result = $stmt->get_result();

            // Hitung total
            $total_penjualan = 0;
            ?>

            <div class="table-responsive">
            <table class="table table-bordered" id="dataTable">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>No Transaksi</th>
            <th>Pembeli</th>
            <th>No. Telepon</th>
            <th>Total</th>
            <th>Bayar</th>
            <th>Kembalian</th>
            <th>Kasir</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while($row = $result->fetch_assoc()): 
            $kembalian = $row['bayar'] - $row['total'];
            $total_penjualan += $row['total'];
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['nama_pembeli'] ?: 'Umum') ?></td>
            <td><?= htmlspecialchars($row['no_telp'] ?: '-') ?></td>
            <td class="text-right"><?= formatRupiah($row['total']) ?></td>
            <td class="text-right"><?= formatRupiah($row['bayar']) ?></td>
            <td class="text-right"><?= formatRupiah($kembalian) ?></td>
            <td><?= getUserName($row['user_id']) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" class="text-right">Total Penjualan:</th>
            <th class="text-right"><?= formatRupiah($total_penjualan) ?></th>
            <th colspan="3"></th>
        </tr>
    </tfoot>
</table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalDetailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function detailTransaksi(id) {
    $.get('get_detail_transaksi.php?id=' + id, function(html) {
        $('#modalDetailContent').html(html);
        $('#modalDetail').modal('show');
    });
}

$(document).ready(function() {
    $('#dataTable').DataTable({
        "order": [[1, "desc"]], // Sort by tanggal desc
        "pageLength": 25
    });
});
</script>

<?php Layout::footer(); ?>
