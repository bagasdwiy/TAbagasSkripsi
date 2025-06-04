<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Toko Saya' ?></title>
    
    <!-- CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Di bagian header -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/jquery/jquery.min.js"></script>
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    
    <!-- jQuery UI untuk autocomplete -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>

.modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.modal-footer .btn {
    padding: 0.375rem 1.75rem;
}

.modal-footer .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.modal-footer .btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.modal-content {
    border-radius: 0.3rem;
    border: none;
}

.modal-header {
    border-bottom: 1px solid #dee2e6;
    padding: 1rem;
}

.modal-body {
    padding: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-control {
    border-radius: 0.25rem;
    border: 1px solid #ced4da;
    padding: 0.375rem 0.75rem;
}
        /* Sidebar Styles */
        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50;
            color: white;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
        }
        
        .sidebar .brand {
            padding: 20px;
            font-size: 24px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px 20px;
            transition: all 0.3s;
            font-size: 16px;
        }
        
        .sidebar a:hover {
            background-color: #34495e;
            padding-left: 25px;
        }
        
        .sidebar a.active {
            background-color: #3498db;
            border-left: 4px solid #fff;
        }
        
        .sidebar a i {
            width: 25px;
            margin-right: 10px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        /* Header User Info Styles */
        .header {
            background: #fff;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .user-card {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background: #f8f9fa;
            border-radius: 50px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 10px;
        }

        .user-name {
            font-weight: 500;
            color: #2c3e50;
        }

        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 20px;
        }

        /* Table Styles */
        .table th {
            background: #f8f9fa;
            border-top: none;
        }

        /* Button Styles */
        .btn-primary {
            background-color: #3498db;
            border: none;
            padding: 8px 20px;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }
         /* Modal Styles */
    .modal-dialog {
        max-width: 500px;
        margin: 1.75rem auto;
    }

    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        min-height: 500px; /* Tambahkan minimum height */
        background-color: #fff;
        border-radius: 0.3rem;
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }

    .modal-body {
        position: relative;
        flex: 1 1 auto;
        padding: 1rem;
    }

    .modal-footer {
        display: flex !important;
        justify-content: flex-end !important;
        padding: 1rem !important;
        border-top: 1px solid #dee2e6 !important;
        background-color: #f8f9fa;
        position: relative !important;
        bottom: 0;
        left: 0;
        right: 0;
    }

    .modal-footer .btn {
        margin-left: 0.5rem;
    }

    /* Form dalam Modal */
    .modal .form-group {
        margin-bottom: 1rem;
    }

    .modal .form-control {
        width: 100%;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }

    .modal label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    /* Button dalam Modal */
    .modal .btn-secondary {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .modal .btn-primary {
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }

    .modal .btn:hover {
        opacity: 0.9;
    }

    /* Memastikan modal scrollable jika konten terlalu panjang */
    .modal-dialog {
        overflow-y: initial !important;
    }

    .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            Toko Teras Yasmin
        </div>
        
        <a href="dashboard.php" <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-home"></i> Dashboard
        </a>
        
        <a href="kasir.php" <?= basename($_SERVER['PHP_SELF']) == 'kasir.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-cash-register"></i> Kasir
        </a>
        
        <a href="barang.php" <?= basename($_SERVER['PHP_SELF']) == 'barang.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-box"></i> Barang
        </a>
        
        <a href="supplier.php" <?= basename($_SERVER['PHP_SELF']) == 'supplier.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-truck"></i> Supplier
        </a>

        <a href="pembeli.php" <?= basename($_SERVER['PHP_SELF']) == 'pembeli.php' ? 'class="active"' : '' ?>>
    <i class="fas fa-users"></i> Data Pembeli
</a>
        
        <a href="retur.php" <?= basename($_SERVER['PHP_SELF']) == 'retur.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-undo"></i> Retur
        </a>
        
        <a href="laporan.php" <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-chart-bar"></i> Laporan
        </a>
        
        <a href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header with User Info -->
        <div class="header">
            <div class="user-info">
                <div class="user-card">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="user-name"><?= $_SESSION['username'] ?? 'User' ?></span>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <div class="container-fluid">
</rewritten_file>