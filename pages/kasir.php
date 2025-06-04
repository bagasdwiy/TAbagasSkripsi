<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

Layout::header('Kasir');
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Kasir</h6>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="cari_barang">Scan Barcode / Nama Barang:</label>
                        <input type="text" id="cari_barang" class="form-control" autofocus 
                            placeholder="Masukkan barcode atau nama barang...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="jumlah">Jumlah:</label>
                        <input type="number" id="jumlah" class="form-control" value="1" min="1">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="keranjang">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td colspan="2"><strong id="total">Rp 0</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-right mt-3">
                <button class="btn btn-danger" onclick="resetKeranjang()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button class="btn btn-primary" onclick="showPembayaran()">
                    <i class="fas fa-money-bill"></i> Bayar
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Pembeli -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="form-group">
            <label for="id_pembeli">Pembeli:</label>
            <select class="form-control select2" id="id_pembeli" name="id_pembeli">
                <option value="">Umum</option>
                <?php
                $pembeli = new Pembeli();
                $result = $pembeli->getAll();
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . 
                         htmlspecialchars($row['nama_pembeli']) . " - " . 
                         htmlspecialchars($row['no_telp']) . "</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>

<!-- Modal Pembayaran -->
<div class="modal fade" id="modalPembayaran" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pembayaran</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Total Belanja:</label>
                    <h3 id="modalTotal">Rp 0</h3>
                </div>
                <div class="form-group">
                    <label for="bayar">Jumlah Bayar:</label>
                    <input type="number" class="form-control" id="bayar">
                </div>
                <div class="form-group">
                    <label>Kembalian:</label>
                    <h3 id="kembalian">Rp 0</h3>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="prosesPembayaran()">Proses</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Focus ke input pencarian
    $('#cari_barang').focus();

    // Handler untuk pencarian barang (tambahkan ini)
    $('#cari_barang').on('keypress', function(e) {
        if (e.which == 13) { // Enter key
            e.preventDefault();
            var keyword = $(this).val();
            
            console.log('Searching:', keyword);
            
            if (keyword) {
                $.ajax({
                    url: 'kasir_action.php',
                    type: 'POST',
                    data: {
                        action: 'search_barang',
                        keyword: keyword
                    },
                    success: function(response) {
                        console.log('Raw response:', response);
                        
                        try {
                            var result = typeof response === 'string' ? JSON.parse(response) : response;
                            console.log('Parsed result:', result);
                            
                            if (result.success) {
                                if (Array.isArray(result.data)) {
                                    if (result.data.length > 1) {
                                        showSearchResults(result.data);
                                    } else {
                                        tambahKeKeranjang(result.data[0]);
                                    }
                                } else {
                                    tambahKeKeranjang(result.data);
                                }
                                $('#cari_barang').val('').focus();
                            } else {
                                alert(result.message || 'Barang tidak ditemukan!');
                                $('#cari_barang').val('').focus();
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            alert('Terjadi kesalahan saat memproses data');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ajax error:', error);
                        console.log('Response:', xhr.responseText);
                        alert('Terjadi kesalahan saat mencari barang');
                    }
                });
            }
        }
    });

    

    // Handler untuk perubahan jumlah
    $(document).on('change', '.qty', function() {
        hitungSubtotal($(this).closest('tr'));
        hitungTotal();
    });

    // Handler untuk hapus item
    $(document).on('click', '.hapus-item', function() {
        $(this).closest('tr').remove();
        hitungTotal();
    });

    // Handler untuk tombol bayar
    $('#tombolBayar').on('click', function() {
        showPembayaran();
    });
});

// Tambahkan fungsi showSearchResults setelah semua fungsi yang ada
function showSearchResults(items) {
    var modalContent = `
        <div class="modal fade" id="searchResultModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Hasil Pencarian</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group">`;
    
    items.forEach(function(item) {
        modalContent += `
            <button type="button" class="list-group-item list-group-item-action" 
                    data-id="${item.id}" 
                    data-nama="${item.nama_barang}" 
                    data-harga="${item.harga}" 
                    data-stok="${item.stok}">
                ${item.nama_barang} - Rp ${formatRupiah(item.harga)} (Stok: ${item.stok})
            </button>`;
    });
    
    modalContent += `
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    
    // Hapus modal lama jika ada
    $('#searchResultModal').remove();
    
    // Tambahkan modal baru
    $('body').append(modalContent);
    
    // Event handler untuk item yang dipilih
    $('#searchResultModal .list-group-item').on('click', function() {
        var item = {
            id: $(this).data('id'),
            nama_barang: $(this).data('nama'),
            harga: $(this).data('harga'),
            stok: $(this).data('stok')
        };
        tambahKeKeranjang(item);
        $('#searchResultModal').modal('hide');
    });
    
    // Tampilkan modal
    $('#searchResultModal').modal('show');
}
// Fungsi tambah ke keranjang
function tambahKeKeranjang(barang) {
    console.log('Data barang:', barang);
    
    var jumlah = parseInt($('#jumlah').val()) || 1;
    var existingRow = $(`#keranjang tbody tr[data-id="${barang.id}"]`);
    
    if (existingRow.length > 0) {
        var qtyInput = existingRow.find('.qty');
        var currentQty = parseInt(qtyInput.val());
        qtyInput.val(currentQty + jumlah).trigger('change');
    } else {
        var row = `
            <tr data-id="${barang.id}">
                <td>${barang.nama_barang}</td>
                <td class="text-right harga">${formatRupiah(barang.harga)}</td>
                <td>
                    <input type="number" class="form-control form-control-sm qty" 
                           value="${jumlah}" min="1" style="width: 80px">
                </td>
                <td class="text-right subtotal">${formatRupiah(barang.harga * jumlah)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm hapus-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#keranjang tbody').append(row);
    }
    
    hitungTotal();
    $('#jumlah').val(1);
}

// Fungsi hitung subtotal per baris
function hitungSubtotal(row) {
    var harga = parseFloat(row.find('.harga').text().replace(/[^\d]/g, ''));
    var qty = parseInt(row.find('.qty').val());
    var subtotal = harga * qty;
    row.find('.subtotal').text(formatRupiah(subtotal));
}

// Fungsi hitung total
function hitungTotal() {
    var total = 0;
    $('#keranjang tbody tr').each(function() {
        var subtotal = parseFloat($(this).find('.subtotal').text().replace(/[^\d]/g, ''));
        total += subtotal;
    });
    $('#total').text(formatRupiah(total));
}

// Fungsi format rupiah
function formatRupiah(angka) {
    return 'Rp ' + parseFloat(angka).toLocaleString('id-ID');
}

// Fungsi tampilkan modal pembayaran
function showPembayaran() {
    var total = parseFloat($('#total').text().replace(/[^\d]/g, ''));
    if (total <= 0) {
        alert('Keranjang masih kosong!');
        return;
    }
    
    $('#modalTotal').text($('#total').text());
    $('#bayar').val('').focus();
    $('#kembalian').text('Rp 0');
    $('#modalPembayaran').modal('show');
}

// Handler untuk input pembayaran
$('#bayar').on('keyup', function() {
    var total = parseFloat($('#total').text().replace(/[^\d]/g, ''));
    var bayar = parseFloat($(this).val()) || 0;
    var kembalian = bayar - total;
    
    $('#kembalian').text(formatRupiah(Math.max(0, kembalian)));
});

// Fungsi proses pembayaran
function prosesPembayaran() {
    var total = parseFloat($('#total').text().replace(/[^\d]/g, ''));
    var bayar = parseFloat($('#bayar').val()) || 0;
    var id_pembeli = $('#id_pembeli').val() || null; // Tambahkan ini
    
    if (bayar < total) {
        alert('Pembayaran kurang!');
        return;
    }
    
    var items = [];
    $('#keranjang tbody tr').each(function() {
        items.push({
            id_barang: $(this).data('id'),
            jumlah: parseInt($(this).find('.qty').val()),
            harga: parseFloat($(this).find('.harga').text().replace(/[^\d]/g, ''))
        });
    });
    
    $.ajax({
        url: 'kasir_action.php',
        type: 'POST',
        data: {
            action: 'simpan_transaksi',
            total: total,
            bayar: bayar,
            id_pembeli: id_pembeli, // Tambahkan ini
            items: JSON.stringify(items)
        },
        dataType: 'json', // Tambahkan ini
        success: function(response) {
            if (response.success) {
                alert('Transaksi berhasil!\nKembalian: ' + formatRupiah(bayar - total));
                $('#modalPembayaran').modal('hide');
                $('#keranjang tbody').empty();
                hitungTotal();
                $('#cari_barang').focus();
                $('#id_pembeli').val('').trigger('change'); // Reset pembeli
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Terjadi kesalahan saat menyimpan transaksi');
            console.error(xhr.responseText);
        }
    });
}
</script>

<?php Layout::footer(); ?> 