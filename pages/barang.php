<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$barang = new Barang();
Layout::header('Data Barang'); 
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Data Barang</h1>
    <button class="btn btn-primary" data-toggle="modal" data-target="#modaltambah">
        <i class="fas fa-plus"></i> Tambah Barang
    </button>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="modaltambah" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah/Update Barang</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formBarang">
                    <div class="form-group">
                        <label>Barcode</label>
                        <input type="text" class="form-control" id="barcode" name="barcode" required>
                        <small class="text-muted">Scan atau masukkan barcode untuk mencari barang</small>
                    </div>
                    <div class="form-group">
                        <label>Nama Barang</label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                    </div>
                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" class="form-control" id="harga" name="harga" required>
                    </div>
                    <div class="form-group">
                        <label>Jumlah Stok yang Ditambahkan</label>
                        <input type="number" class="form-control" id="stok" name="stok" min="1" required>
                        <small class="text-muted">Masukkan jumlah stok yang akan ditambahkan</small>
                    </div>
                    <div class="form-group">
                        <label>Supplier</label>
                        <select class="form-control" id="id_supplier" name="id_supplier" required>
                            <option value="">Pilih Supplier</option>
                            <?php
                            $suppliers = $db->query("SELECT * FROM supplier ORDER BY nama_supplier");
                            while ($supplier = $suppliers->fetch_assoc()) {
                                echo "<option value='" . $supplier['id'] . "'>" . htmlspecialchars($supplier['nama_supplier']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="tombolSimpan">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Barcode</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Supplier</th>
                        <th>Terakhir Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // DataTable
    var table = $('#dataTable').DataTable({
        processing: true,
        ajax: {
            url: 'get_barang.php',
            dataSrc: 'data'
        },
        columns: [
            { data: 'DT_RowIndex' },
            { data: 'nama_barang' },
            { data: 'barcode' },
            { data: 'harga' },
            { data: 'stok' },
            { data: 'nama_supplier' },
            { data: 'tanggal_update' },
            { data: 'actions' }
        ]
    });

// Handler untuk input barcode
$('#barcode').on('change', function() {
    var barcode = $(this).val();
    if (barcode) {
        $.ajax({
            url: 'barang_action.php',
            type: 'POST',
            data: { 
                action: 'get_by_barcode',
                barcode: barcode 
            },
            dataType: 'json', // Tambahkan ini
            success: function(response) {
                if (response.success) {
                    $('#nama_barang').val(response.data.nama_barang);
                    $('#harga').val(response.data.harga);
                    $('#id_supplier').val(response.data.id_supplier);
                    $('#stok').focus();
                }
            }
        });
    }
});

    // Handler untuk input nama barang
    $('#nama_barang').on('change', function() {
    var nama = $(this).val();
    if (nama) {
        $.ajax({
            url: 'barang_action.php',
            type: 'POST',
            data: { 
                action: 'get_by_nama',
                nama_barang: nama 
            },
            dataType: 'json', // Tambahkan ini
            success: function(response) {
                if (response.success) {
                    $('#barcode').val(response.data.barcode);
                    $('#harga').val(response.data.harga);
                    $('#id_supplier').val(response.data.id_supplier);
                    $('#stok').focus();
                }
            }
        });
    }
});

    // Handler untuk tombol simpan
    $('#tombolSimpan').on('click', function() {
    var formData = {
        action: 'create',
        nama_barang: $('#nama_barang').val(),
        barcode: $('#barcode').val(),
        harga: $('#harga').val(),
        stok: $('#stok').val(),
        id_supplier: $('#id_supplier').val()
    };

    $.ajax({
        url: 'barang_action.php',
        type: 'POST',
        data: formData,
        dataType: 'json', // Tambahkan ini
        success: function(response) {
            if (response.success) {
                alert(response.message);
                // Bersihkan form
                $('#formBarang')[0].reset();
                // Tutup modal dengan benar
                $('#modaltambah').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                // Reload table
                table.ajax.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Terjadi kesalahan: ' + error);
            console.log(xhr.responseText);
        }
        });
    });
}); 
$('#modaltambah').on('hidden.bs.modal', function () {
    $('#formBarang')[0].reset();
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
});
// Tambahkan fungsi ini di dalam $(document).ready()
function editBarang(id) {
    $.ajax({
        url: 'barang_action.php',
        type: 'POST',
        data: {
            action: 'get_by_id',
            id: id
        },
        success: function(response) {
            if (response.success) {
                $('#barcode').val(response.data.barcode);
                $('#nama_barang').val(response.data.nama_barang);
                $('#harga').val(response.data.harga);
                $('#id_supplier').val(response.data.id_supplier);
                $('#stok').val('0'); // Set 0 karena hanya bisa menambah
                $('#modaltambah').modal('show');
            } else {
                alert('Error: ' + response.message);
            }
        }
    });
}

function deleteBarang(id) {
    if (confirm('Apakah Anda yakin ingin menghapus barang ini?')) {
        $.ajax({
            url: 'barang_action.php',
            type: 'POST',
            data: {
                action: 'delete',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#dataTable').DataTable().ajax.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>

<?php Layout::footer(); ?>