// Barcode Scanner Implementation
function startScanner() {
    Quagga.init({
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: document.querySelector("#interactive"),
            constraints: {
                facingMode: "environment"
            },
        },
        decoder: {
            readers: ["ean_reader", "ean_8_reader", "code_128_reader", "code_39_reader", "upc_reader"]
        }
    }, function(err) {
        if (err) {
            console.error(err);
            return;
        }
        document.getElementById('scanner-container').style.display = 'block';
        Quagga.start();
    });

    Quagga.onDetected(function(result) {
        let code = result.codeResult.code;
        document.getElementById('barcode').value = code;
        stopScanner();
    });
}

function stopScanner() {
    Quagga.stop();
    document.getElementById('scanner-container').style.display = 'none';
}

// Modal functionality
function editBarang(barang) {
    const modal = document.getElementById('editModal');
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit Barang</h3>
            <form method="POST" class="form">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="${barang.id}">
                
                <div class="form-group">
                    <label>Barcode:</label>
                    <input type="text" name="barcode" value="${barang.barcode}" required>
                </div>
                
                <div class="form-group">
                    <label>Nama Barang:</label>
                    <input type="text" name="nama_barang" value="${barang.nama_barang}" required>
                </div>
                
                <div class="form-group">
                    <label>Harga:</label>
                    <input type="number" name="harga" value="${barang.harga}" required>
                </div>
                
                <div class="form-group">
                    <label>Stok:</label>
                    <input type="number" name="stok" value="${barang.stok}" required>
                </div>
                
                <button type="submit" class="btn-primary">Update Barang</button>
            </form>
        </div>
    `;
    modal.style.display = "block";

    // Close button functionality
    const span = modal.querySelector('.close');
    span.onclick = function() {
        modal.style.display = "none";
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
} 