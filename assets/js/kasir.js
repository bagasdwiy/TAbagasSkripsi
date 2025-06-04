let items = [];

document.getElementById('barcode').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        let barcode = this.value;
        
        fetch('kasir.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'cari_barcode=1&barcode=' + barcode
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            addToCart(data);
            this.value = '';
        });
    }
});

function addToCart(barang) {
    let existingItem = items.find(item => item.id === barang.id);
    if (existingItem) {
        existingItem.jumlah++;
    } else {
        items.push({
            id: barang.id,
            nama_barang: barang.nama_barang,
            harga: parseFloat(barang.harga),
            jumlah: 1
        });
    }
    updateCart();
}

function removeItem(index) {
    items.splice(index, 1);
    updateCart();
}

function updateCart() {
    let tbody = document.getElementById('cart-items');
    let total = 0;
    tbody.innerHTML = '';
    
    items.forEach((item, index) => {
        let subtotal = item.harga * item.jumlah;
        total += subtotal;
        
        tbody.innerHTML += `
            <tr>
                <td>${item.nama_barang}</td>
                <td>Rp ${item.harga.toLocaleString()}</td>
                <td>${item.jumlah}</td>
                <td>Rp ${subtotal.toLocaleString()}</td>
                <td>
                    <button onclick="removeItem(${index})" class="btn-danger">Hapus</button>
                </td>
            </tr>
        `;
    });
    
    document.getElementById('total-amount').textContent = total.toLocaleString();
}

function simpanTransaksi() {
    if (items.length === 0) {
        alert('Keranjang kosong!');
        return;
    }

    fetch('kasir.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'simpan_transaksi=1&items=' + JSON.stringify(items) + 
              '&total=' + document.getElementById('total-amount').textContent.replace(/,/g, '')
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        alert('Transaksi berhasil disimpan!');
        items = [];
        updateCart();
        document.getElementById('barcode').focus();
    });
}
