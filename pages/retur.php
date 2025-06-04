<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

Layout::header('Data Retur'); 
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Data Retur</h1>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Data Retur</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modaltambah">
                <i class="fas fa-plus"></i> Tambah Retur
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Barang</th>
                        <th>Jumlah</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modaltambah" tabindex="-1" role="dialog" aria-labelledby="modaltambahLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modaltambahLabel">Tambah Retur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Barang</label>
                    <select class="form-control" id="id_barang" name="id_barang" required>
                        <option value="">Pilih Barang</option>
                        <?php
                        $barang = $db->query("SELECT * FROM barang ORDER BY nama_barang");
                        while ($row = $barang->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['nama_barang']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah</label>
                    <input type="number" class="form-control" id="jumlah" name="jumlah" required>
                </div>
                <div class="form-group">
                    <label>Alasan</label>
                    <textarea class="form-control" id="alasan" name="alasan" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="tombolSimpan">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modaledit" tabindex="-1" role="dialog" aria-labelledby="modaleditLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modaleditLabel">Edit Retur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <div class="form-group">
                    <label>Barang</label>
                    <select class="form-control" id="edit_id_barang" name="id_barang" required>
                        <option value="">Pilih Barang</option>
                        <?php
                        $barang->data_seek(0);
                        while ($row = $barang->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['nama_barang']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah</label>
                    <input type="number" class="form-control" id="edit_jumlah" name="jumlah" required>
                </div>
                <div class="form-group">
                    <label>Alasan</label>
                    <textarea class="form-control" id="edit_alasan" name="alasan" required></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" id="edit_status" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="tombolUpdate">Update</button>
            </div>
        </div>
    </div>
</div>

<script>
// Fungsi untuk edit retur
function editRetur(id) {
    $.ajax({
        url: 'retur_action.php',
        type: 'POST',
        data: {
            action: 'get',
            id: id
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.success) {
                $('#edit_id').val(data.data.id);
                $('#edit_id_barang').val(data.data.id_barang);
                $('#edit_jumlah').val(data.data.jumlah);
                $('#edit_alasan').val(data.data.alasan);
                $('#edit_status').val(data.data.status);
                $('#modaledit').modal('show');
            } else {
                alert('Error: ' + data.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Terjadi kesalahan: ' + error);
        }
    });
}

// Fungsi untuk delete retur
function deleteRetur(id) {
    if (confirm('Apakah Anda yakin ingin menghapus retur ini?')) {
        $.ajax({
            url: 'retur_action.php',
            type: 'POST',
            data: {
                action: 'delete',
                id: id
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    alert(result.message);
                    $('#dataTable').DataTable().ajax.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Terjadi kesalahan: ' + error);
            }
        });
    }
}

$(document).ready(function() {
    // Inisialisasi DataTable
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "get_retur.php",
            "type": "POST"
        },
        "columns": [
            { 
                "data": null,
                "render": function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { "data": "tanggal" },
            { "data": "nama_barang" },
            { "data": "jumlah" },
            { "data": "alasan" },
            { 
                "data": "status",
                "render": function(data, type, row) {
                    var badge = '';
                    switch(data) {
                        case 'pending':
                            badge = 'warning';
                            break;
                        case 'disetujui':
                            badge = 'success';
                            break;
                        case 'ditolak':
                            badge = 'danger';
                            break;
                    }
                    return '<span class="badge badge-' + badge + '">' + data + '</span>';
                }
            },
            { "data": "actions" }
        ],
        "language": {
            "url": "../assets/vendor/datatables/Indonesian.json"
        }
    });

    // Handler untuk tombol simpan
    $('#tombolSimpan').on('click', function() {
        var formData = {
            action: 'create',
            id_barang: $('#id_barang').val(),
            jumlah: $('#jumlah').val(),
            alasan: $('#alasan').val()
        };

        $.ajax({
            url: 'retur_action.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    alert(result.message);
                    $('#modaltambah').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    table.ajax.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Terjadi kesalahan: ' + error);
            }
        });
    });

    // Handler untuk tombol update
    $('#tombolUpdate').on('click', function() {
        var formData = {
            action: 'update',
            id: $('#edit_id').val(),
            id_barang: $('#edit_id_barang').val(),
            jumlah: $('#edit_jumlah').val(),
            alasan: $('#edit_alasan').val(),
            status: $('#edit_status').val()
        };

        $.ajax({
            url: 'retur_action.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    alert(result.message);
                    $('#modaledit').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    table.ajax.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Terjadi kesalahan: ' + error);
            }
        });
    });
});
</script>

<?php Layout::footer(); ?> 