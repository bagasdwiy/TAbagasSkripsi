<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$db = Database::getInstance()->getConnection();

// Ambil tanggal dari parameter URL
$tanggal = isset($_GET['id']) ? $_GET['id'] : date('Y-m-d');


// Query untuk mengambil semua transaksi pada tanggal tersebut
$query = "SELECT 
            t.id,
            TIME(t.tanggal) as waktu,
            b.nama_barang,
            b.barcode,
            dt.jumlah,
            dt.harga,
            dt.subtotal,
            t.total as total_bayar,
            t.bayar,
            (t.bayar - t.total) as kembalian
          FROM transaksi t
          INNER JOIN detail_transaksi dt ON t.id = dt.id_transaksi
          INNER JOIN barang b ON dt.id_barang = b.id
          WHERE DATE(t.tanggal) = ?
          ORDER BY t.tanggal DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("s", $tanggal);
$stmt->execute();
$result = $stmt->get_result();



// Reset result pointer
$result->data_seek(0);

Layout::header('Detail Transaksi');
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Detail Transaksi - <?= tanggalIndo($tanggal) ?></h6>
        <a href="laporan.php" class="btn btn-secondary">Kembali</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Waktu</th>
                        <th>Nama Barang</th>
                        <th>Barcode</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Total Bayar</th>
                        <th>Kembalian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0):
                        $no = 1;
                        $current_id = null;
                        while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['waktu'] ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td><?= htmlspecialchars($row['barcode']) ?></td>
                            <td><?= $row['jumlah'] ?></td>
                            <td><?= formatRupiah($row['harga']) ?></td>
                            <td><?= formatRupiah($row['subtotal']) ?></td>
                            <td><?= ($current_id != $row['id']) ? formatRupiah($row['total_bayar']) : '' ?></td>
                            <td><?= ($current_id != $row['id']) ? formatRupiah($row['kembalian']) : '' ?></td>
                        </tr>
                    <?php 
                            $current_id = $row['id'];
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data transaksi pada tanggal ini</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php Layout::footer(); ?> 