<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    exit('Unauthorized');
}

$retur = new Retur();
try {
    $detail = $retur->getDetailById($_GET['id']);
} catch (Exception $e) {
    exit($e->getMessage());
}
?>

<div class="table-responsive">
    <table class="table">
        <tr>
            <th width="200">ID Retur</th>
            <td><?= $detail['id'] ?></td>
        </tr>
        <tr>
            <th>Tanggal</th>
            <td><?= date('d/m/Y H:i', strtotime($detail['tanggal'])) ?></td>
        </tr>
        <tr>
            <th>Supplier</th>
            <td><?= htmlspecialchars($detail['nama_supplier']) ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                <span class="badge badge-<?= getStatusBadge($detail['status']) ?>">
                    <?= ucfirst($detail['status'] ?? 'pending') ?>
                </span>
            </td>
        </tr>
        <tr>
            <th>Dibuat Oleh</th>
            <td><?= htmlspecialchars($detail['nama_user']) ?></td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td><?= nl2br(htmlspecialchars($detail['keterangan'])) ?></td>
        </tr>
    </table>

    <h6 class="font-weight-bold mt-4">Detail Item</h6>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Barang</th>
                <th>Barcode</th>
                <th>Jumlah</th>
                <th>Alasan</th>
                <th>Foto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detail['items'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                    <td><?= htmlspecialchars($item['barcode']) ?></td>
                    <td><?= number_format($item['jumlah']) ?></td>
                    <td><?= htmlspecialchars($item['alasan']) ?></td>
                    <td>
                        <?php if ($item['foto']): ?>
                            <a href="../uploads/retur/<?= $item['foto'] ?>" target="_blank">
                                <img src="../uploads/retur/<?= $item['foto'] ?>" height="50" class="img-thumbnail">
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Tidak ada foto</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
function getStatusBadge($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'diproses':
            return 'info';
        case 'selesai':
            return 'success';
        case 'ditolak':
            return 'danger';
        default:
            return 'secondary';
    }
}
?> 