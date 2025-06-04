<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
global $conn;

if (!$conn) {
    die("Database connection failed");
}

// Fungsi untuk mengecek apakah supplier masih digunakan
function isSupplierInUse($db, $id) {
    // Cek di tabel barang
    $query = "SELECT COUNT(*) as count FROM barang WHERE id_supplier = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) return true;

    // Cek di tabel retur
    $query = "SELECT COUNT(*) as count FROM retur WHERE id_supplier = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) return true;

    return false;
}

// Pada bagian delete supplier
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    
    // Cek apakah supplier masih digunakan
    if (isSupplierInUse($db, $id)) {
        flashMessage('Supplier tidak dapat dihapus karena masih digunakan', 'error');
    } else {
        $query = "DELETE FROM supplier WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            flashMessage('Supplier berhasil dihapus', 'success');
        } else {
            flashMessage('Gagal menghapus supplier', 'error');
        }
    }
    
    header("Location: supplier.php");
    exit;
}
// Bagian atas cek karena harus ada perbaikan pada retur


// Proses tambah/edit/hapus supplier
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $nama = $_POST['nama_supplier'];
        $kontak = $_POST['kontak'];
        $alamat = $_POST['alamat'];
        $telepon = $_POST['telepon'];
        $email = $_POST['email'];

        $query = "INSERT INTO supplier (nama_supplier, kontak, alamat, telepon, email) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssss", $nama, $kontak, $alamat, $telepon, $email);
        
        if ($stmt->execute()) {
            header("Location: supplier.php?success=added");
            exit;
        } else {
            die("Execute failed: " . $stmt->error);
        }
    }
    
    if (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $nama = $_POST['nama_supplier'];
        $kontak = $_POST['kontak'];
        $alamat = $_POST['alamat'];
        $telepon = $_POST['telepon'];
        $email = $_POST['email'];

        $query = "UPDATE supplier 
                 SET nama_supplier = ?, kontak = ?, alamat = ?, telepon = ?, email = ? 
                 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $nama, $kontak, $alamat, $telepon, $email, $id);
        
        if ($stmt->execute()) {
            header("Location: supplier.php?success=updated");
            exit;
        }
    }
}

// Hapus supplier
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Cek apakah supplier masih digunakan
    $check = $conn->query("SELECT COUNT(*) as count FROM barang WHERE id_supplier = $id")->fetch_assoc();
    if ($check['count'] > 0) {
        header("Location: supplier.php?error=used");
        exit;
    }
    
    $query = "DELETE FROM supplier WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: supplier.php?success=deleted");
        exit;
    }
}

// Ambil daftar supplier
$query = "SELECT * FROM supplier ORDER BY nama_supplier";
$result = $conn->query($query);

if (!$result) {
    die("Error: " . $conn->error);
}

Layout::header('Supplier');
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Data Supplier</h1>

    <!-- Alert Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            switch ($_GET['success']) {
                case 'added':
                    echo "Supplier berhasil ditambahkan!";
                    break;
                case 'updated':
                    echo "Supplier berhasil diupdate!";
                    break;
                case 'deleted':
                    echo "Supplier berhasil dihapus!";
                    break;
            }
            ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">
                <i class="fas fa-plus"></i> Tambah Supplier
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nama Supplier</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nama_supplier']) ?></td>
                                <td><?= htmlspecialchars($row['alamat']) ?></td>
                                <td><?= htmlspecialchars($row['telepon']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="editSupplier(<?= $row['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteSupplier(<?= $row['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Supplier</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Supplier</label>
                        <input type="text" class="form-control" name="nama_supplier" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea class="form-control" name="alamat" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" class="form-control" name="telepon">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="add" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Supplier</label>
                        <input type="text" class="form-control" name="nama_supplier" id="edit_nama" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea class="form-control" name="alamat" id="edit_alamat" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" class="form-control" name="telepon" id="edit_telepon">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSupplier(id) {
    // Ambil data supplier dengan AJAX
    $.ajax({
        url: 'get_supplier.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(data) {
            $('#edit_id').val(data.id);
            $('#edit_nama').val(data.nama_supplier);
            $('#edit_kontak').val(data.kontak);
            $('#edit_alamat').val(data.alamat);
            $('#edit_telepon').val(data.telepon);
            $('#edit_email').val(data.email);
            $('#editModal').modal('show');
        }
    });
}

function deleteSupplier(id) {
    if (confirm('Apakah Anda yakin ingin menghapus supplier ini?')) {
        window.location.href = 'supplier.php?delete=' + id;
    }
}

// Inisialisasi DataTable
$(document).ready(function() {
    $('#dataTable').DataTable();
});
</script>

<?php Layout::footer(); ?>
