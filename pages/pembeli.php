<?php
require_once '../includes/init.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$pembeli = new Pembeli();
Layout::header('Data Pembeli'); 
?>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Data Pembeli</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalPembeli">
                <i class="fas fa-plus"></i> Tambah Pembeli
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pembeli</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="modalPembeli" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah/Edit Pembeli</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formPembeli">
                    <input type="hidden" id="id" name="id">
                    <div class="form-group">
                        <label>Nama Pembeli</label>
                        <input type="text" class="form-control" id="nama_pembeli" name="nama_pembeli" required>
                    </div>
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" class="form-control" id="no_telp" name="no_telp" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" required></textarea>
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

<script>
$(document).ready(function() {
    // DataTable
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: 'get_pembeli.php',
            type: 'POST',
            dataSrc: 'data'
        },
        columns: [
            { data: 'DT_RowIndex' },
            { data: 'nama_pembeli' },
            { data: 'no_telp' },
            { data: 'alamat' },
            { data: 'actions', orderable: false, searchable: false }
        ]
    });

    // Handler untuk tombol simpan
    $('#tombolSimpan').on('click', function() {
        var formData = {
            action: $('#id').val() ? 'update' : 'create',
            id: $('#id').val(),
            nama_pembeli: $('#nama_pembeli').val(),
            no_telp: $('#no_telp').val(),
            alamat: $('#alamat').val()
        };

        $.ajax({
            url: 'pembeli_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    // Tutup modal dengan benar
                    $('#modalPembeli').modal('hide');
                    // Hapus backdrop dan class modal
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    // Reset form
                    $('#formPembeli')[0].reset();
                    $('#id').val('');
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

    // Handler untuk modal ditutup
    $('#modalPembeli').on('hidden.bs.modal', function () {
        // Hapus backdrop dan class modal
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        // Reset form
        $('#formPembeli')[0].reset();
        $('#id').val('');
    });
});

// Fungsi untuk edit
function editPembeli(id) {
    $.ajax({
        url: 'pembeli_action.php',
        type: 'POST',
        data: {
            action: 'get_by_id',
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#id').val(response.data.id);
                $('#nama_pembeli').val(response.data.nama_pembeli);
                $('#no_telp').val(response.data.no_telp);
                $('#alamat').val(response.data.alamat);
                $('#modalPembeli').modal('show');
            } else {
                alert('Error: ' + response.message);
            }
        }
    });
}

// Fungsi untuk hapus
function deletePembeli(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        $.ajax({
            url: 'pembeli_action.php',
            type: 'POST',
            data: {
                action: 'delete',
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    table.ajax.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>

<?php Layout::footer(); ?>