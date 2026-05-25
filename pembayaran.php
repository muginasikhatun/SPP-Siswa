<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'config/koneksi.php';

// Handle AJAX request for getting SPP data based on NISN
if (isset($_GET['get_data']) && $_GET['get_data'] == 'true' && isset($_GET['nisn'])) {
    $nisn = $_GET['nisn'];
    
    $query = "SELECT s.nisn, s.nama, s.id_spp, sp.nominal 
              FROM tb_siswa s 
              JOIN tb_spp sp ON s.id_spp = sp.id_spp 
              WHERE s.nisn = '$nisn'";
    
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Generate ID pembayaran otomatis
        $query_max = "SELECT MAX(id_pembayaran) as max_id FROM tb_pembayaran";
        $result_max = mysqli_query($conn, $query_max);
        $row_max = mysqli_fetch_assoc($result_max);
        $max_id = $row_max['max_id'];
        if ($max_id) {
            $num = (int)substr($max_id, 3) + 1;
            $id_pembayaran = 'BYR' . str_pad($num, 3, '0', STR_PAD_LEFT);
        } else {
            $id_pembayaran = 'BYR001';
        }
        
        echo json_encode([
            'nama' => $row['nama'],
            'id_spp' => $row['id_spp'],
            'nominal' => $row['nominal'],
            'id_pembayaran' => $id_pembayaran
        ]);
    } else {
        echo json_encode(['nama' => null]);
    }
    exit();
}

if (isset($_POST['tambah'])) {
    $id_pembayaran = $_POST['id_pembayaran'];
    $status = $_POST['status'];
    $nisn = $_POST['nisn'];
    
    // Handle date fields - set to NULL if status is Belum Lunas
    if ($status == 'Sudah Lunas') {
        $tgl_bayar = "'" . $_POST['tgl_bayar'] . "'";
        $tgl_terakhir_bayar = "'" . $_POST['tgl_terakhir_bayar'] . "'";
        $batas_pembayaran = "'" . $_POST['batas_pembayaran'] . "'";
    } else {
        $tgl_bayar = 'NULL';
        $tgl_terakhir_bayar = 'NULL';
        $batas_pembayaran = 'NULL';
    }
    
    $jumlah_bulan = $_POST['jumlah_bulan'];
    $id_spp = $_POST['id_spp'];
    $nominal_bayar = $_POST['nominal_bayar'];
    
    // Handle jumlah_bayar and kembalian - set to 0 if status is Belum Lunas
    if ($status == 'Sudah Lunas') {
        $jumlah_bayar = $_POST['jumlah_bayar'];
        $kembalian = $_POST['kembalian'];
    } else {
        $jumlah_bayar = 0;
        $kembalian = 0;
    }
    
    $query = "INSERT INTO tb_pembayaran VALUES ('$id_pembayaran', '$status', '$nisn', $tgl_bayar, $tgl_terakhir_bayar, $batas_pembayaran, '$jumlah_bulan', '$id_spp', '$nominal_bayar', '$jumlah_bayar', '$kembalian')";
    mysqli_query($conn, $query);
    header("Location: pembayaran.php");
}

if (isset($_POST['edit'])) {
    $id_pembayaran = $_POST['id_pembayaran'];
    $status = $_POST['status'];
    $nisn = $_POST['nisn'];
    $tgl_bayar = $_POST['tgl_bayar'];
    $tgl_terakhir_bayar = $_POST['tgl_terakhir_bayar'];
    $batas_pembayaran = $_POST['batas_pembayaran'];
    $jumlah_bulan = $_POST['jumlah_bulan'];
    $id_spp = $_POST['id_spp'];
    $nominal_bayar = $_POST['nominal_bayar'];
    $jumlah_bayar = $_POST['jumlah_bayar'];
    $kembalian = $_POST['kembalian'];
    
    $query = "UPDATE tb_pembayaran SET status='$status', nisn='$nisn', tgl_bayar='$tgl_bayar', tgl_terakhir_bayar='$tgl_terakhir_bayar', batas_pembayaran='$batas_pembayaran', jumlah_bulan='$jumlah_bulan', id_spp='$id_spp', nominal_bayar='$nominal_bayar', jumlah_bayar='$jumlah_bayar', kembalian='$kembalian' WHERE id_pembayaran='$id_pembayaran'";
    mysqli_query($conn, $query);
    header("Location: pembayaran.php");
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM tb_pembayaran WHERE id_pembayaran='$id'");
    header("Location: pembayaran.php");
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $query = "SELECT p.*, s.nama FROM tb_pembayaran p JOIN tb_siswa s ON p.nisn = s.nisn WHERE p.id_pembayaran LIKE '%$search%' OR p.nisn LIKE '%$search%' OR p.status LIKE '%$search%' OR s.nama LIKE '%$search%' ORDER BY p.tgl_bayar DESC";
} else {
    $query = "SELECT p.*, s.nama FROM tb_pembayaran p JOIN tb_siswa s ON p.nisn = s.nisn ORDER BY p.tgl_bayar DESC";
}

// Hitung total data
$total_data = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_pembayaran"));
$total_lunas = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_pembayaran WHERE status='Sudah Lunas'"));
$total_belum = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_pembayaran WHERE status='Belum Lunas'"));
$total_nominal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal_bayar) as total FROM tb_pembayaran"))['total'];
$total_nominal = $total_nominal ? $total_nominal : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - SPP Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            margin-bottom: 8px;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar h4 {
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .user-info {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .badge-level {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .level-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .level-petugas {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            color: white;
        }
        
        .btn-tambah {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-tambah:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .card-dashboard {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-bottom: none;
        }
        
        .search-box {
            background: white;
            border-radius: 50px;
            padding: 5px 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .btn-cari {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-cari:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .table-custom {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .table-custom thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table-custom thead th {
            padding: 15px;
            font-weight: 600;
            border: none;
        }
        
        .table-custom tbody tr {
            transition: all 0.3s;
        }
        
        .table-custom tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
            transform: scale(1.01);
        }
        
        .btn-action {
            border-radius: 8px;
            padding: 6px 12px;
            margin: 0 3px;
            transition: all 0.3s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        .modal-content-custom {
            border-radius: 20px;
            overflow: hidden;
        }
        
        .modal-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-bottom: none;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 14px;
        }
        
        .badge-pembayaran {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .status-lunas {
            background: #d4edda;
            color: #155724;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-belum {
            background: #f8d7da;
            color: #721c24;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .info-siswa {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            display: none;
            border-left: 4px solid #28a745;
        }
        
        .info-siswa.show {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }
        
        .date-fields {
            display: none;
        }
        
        .date-fields.show {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate {
            animation: fadeInUp 0.5s ease-out;
        }
        
        .nominal-text {
            font-weight: bold;
            color: #28a745;
        }
        
        .badge-bulan {
            background: #17a2b8;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-3">
                <h4 class="text-center mb-4">
                    <i class="fas fa-graduation-cap me-2"></i>
                    SPP Siswa
                </h4>
                <div class="user-info">
                    <i class="fas fa-user-circle fa-2x mb-2"></i>
                    <p class="mb-0 small">Selamat datang,</p>
                    <strong><?php echo $_SESSION['nama_petugas']; ?></strong>
                    <br>
                    <span class="badge-level level-<?php echo strtolower($_SESSION['level']); ?> mt-2 d-inline-block">
                        <i class="fas fa-shield-alt me-1"></i>
                        <?php echo strtoupper($_SESSION['level']); ?>
                    </span>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
                    <a class="nav-link" href="kelas.php"><i class="fas fa-school me-2"></i>Data Kelas</a>
                    <a class="nav-link" href="spp.php"><i class="fas fa-money-bill me-2"></i>Data SPP</a>
                    <a class="nav-link" href="petugas.php"><i class="fas fa-users me-2"></i>Data Petugas</a>
                    <a class="nav-link" href="siswa.php"><i class="fas fa-user-graduate me-2"></i>Data Siswa</a>
                    <a class="nav-link active" href="pembayaran.php"><i class="fas fa-credit-card me-2"></i>Pembayaran</a>
                    <a class="nav-link" href="cek_pembayaran.php"><i class="fas fa-search me-2"></i>Cek Pembayaran</a>
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 animate">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-credit-card me-2 text-primary"></i>
                            Pembayaran SPP
                        </h2>
                        <p class="text-muted mb-0">Kelola transaksi pembayaran SPP siswa</p>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <?php echo date('l, d F Y'); ?>
                    </div>
                </div>
                
                <!-- Tombol Tambah -->
                <div class="mb-4 animate">
                    <button class="btn btn-tambah text-white" onclick="openAddModal()">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Pembayaran Baru
                    </button>
                </div>
                
                <!-- Tabel Data Pembayaran -->
                <div class="card-dashboard animate">
                    <div class="card-header-custom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i>
                                Daftar Pembayaran
                            </h5>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-database me-1"></i>
                                <?php echo $total_data; ?> Data
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Search Box -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="search-box">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-0">
                                            <i class="fas fa-search text-primary"></i>
                                        </span>
                                        <input type="text" id="searchInput" class="form-control border-0" 
                                               placeholder="Cari pembayaran berdasarkan ID, NISN, Nama Siswa, atau Status..." 
                                               value="<?php echo $search; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button onclick="searchData()" class="btn btn-cari text-white w-100">
                                    <i class="fas fa-search me-2"></i> Cari
                                </button>
                            </div>
                        </div>
                        
                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                                <thead>
                                    <tr>
                                        <th width="10%">ID</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">NISN</th>
                                        <th width="15%">Nama Siswa</th>
                                        <th width="10%">Tgl Bayar</th>
                                        <th width="8%">Bulan</th>
                                        <th width="12%">Nominal</th>
                                        <th width="10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = mysqli_query($conn, $query);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $statusClass = $row['status'] == 'Sudah Lunas' ? 'status-lunas' : 'status-belum';
                                            $statusIcon = $row['status'] == 'Sudah Lunas' ? 'fa-check-circle' : 'fa-exclamation-circle';
                                    ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $row['id_pembayaran']; ?></td>
                                        <td>
                                            <span class="<?php echo $statusClass; ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $row['nisn']; ?></td>
                                        <td><strong><?php echo $row['nama']; ?></strong></td>
                                        <td><?php echo $row['tgl_bayar'] ? date('d/m/Y', strtotime($row['tgl_bayar'])) : '-'; ?></td>
                                        <td><?php echo $row['jumlah_bulan']; ?> bln</td>
                                        <td class="nominal-text">Rp <?php echo number_format((float)$row['nominal_bayar'], 0, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <button onclick="editPembayaran('<?php echo $row['id_pembayaran']; ?>', '<?php echo $row['status']; ?>', '<?php echo $row['nisn']; ?>', '<?php echo $row['tgl_bayar']; ?>', '<?php echo $row['tgl_terakhir_bayar']; ?>', '<?php echo $row['batas_pembayaran']; ?>', '<?php echo $row['jumlah_bulan']; ?>', '<?php echo $row['id_spp']; ?>', '<?php echo $row['nominal_bayar']; ?>', '<?php echo $row['jumlah_bayar']; ?>', '<?php echo $row['kembalian']; ?>')" 
                                                    class="btn btn-warning btn-action" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="pembayaran.php?hapus=<?php echo $row['id_pembayaran']; ?>" 
                                               class="btn btn-danger btn-action" 
                                               onclick="return confirm('Yakin ingin menghapus data pembayaran ini?')" 
                                               title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <h6 class="text-muted">Belum ada data pembayaran</h6>
                                            <p class="text-muted small">Klik tombol "Tambah Pembayaran Baru" untuk menambahkan transaksi</p>
                                         </span>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-custom">
                <div class="modal-header-custom">
                    <h5 class="modal-title">
                        Tambah Pembayaran Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" id="formPembayaran">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">
                                    ID Pembayaran
                                </label>
                                <input type="text" name="id_pembayaran" id="id_pembayaran" class="form-control" readonly>
                                <small class="text-muted">Otomatis terisi saat NISN dimasukkan</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">
                                    Status
                                </label>
                                <select name="status" id="status" class="form-control" required onchange="toggleDateFields()">
                                    <option value="Belum Lunas">⚠️ Belum Lunas</option>
                                    <option value="Sudah Lunas">✅ Sudah Lunas</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">
                                    NISN
                                </label>
                                <input type="text" name="nisn" id="nisn" class="form-control" placeholder="Masukkan NISN" required>
                            </div>
                        </div>
                        
                        <!-- Informasi Siswa -->
                        <div id="infoSiswa" class="info-siswa">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Nama Siswa:</strong> <span id="nama_siswa"></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>ID SPP:</strong> <span id="display_id_spp"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">
                                    ID SPP
                                </label>
                                <input type="text" name="id_spp" id="id_spp" class="form-control" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">
                                    Nominal per Bulan
                                </label>
                                <input type="text" id="nominal_per_bulan" class="form-control" readonly>
                                <input type="hidden" name="nominal_bayar" id="nominal_bayar_hidden">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">
                                    Jumlah Bulan
                                </label>
                                <input type="number" name="jumlah_bulan" id="jumlah_bulan" class="form-control" min="1" placeholder="Contoh: 6" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">
                                    Total Harus Dibayar
                                </label>
                                <input type="text" id="total_harus_bayar" class="form-control" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">
                                    Jumlah Bayar
                                </label>
                                <input type="number" name="jumlah_bayar" id="jumlah_bayar" class="form-control" placeholder="Masukkan jumlah bayar">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">
                                    Kembalian
                                </label>
                                <input type="text" name="kembalian" id="kembalian" class="form-control" readonly>
                            </div>
                        </div>
                        
                        <!-- Date Fields - Only show when status is Sudah Lunas -->
                        <div id="dateFields" class="date-fields">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">
                                        Tanggal Bayar
                                    </label>
                                    <input type="date" name="tgl_bayar" id="tgl_bayar" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">
                                        Tanggal Terakhir Bayar
                                    </label>
                                    <input type="date" name="tgl_terakhir_bayar" id="tgl_terakhir_bayar" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">
                                        Batas Pembayaran
                                    </label>
                                    <input type="date" name="batas_pembayaran" id="batas_pembayaran" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" name="tambah" class="btn btn-primary">
                                Simpan Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-custom">
                <div class="modal-header-custom">
                    <h5 class="modal-title">
                        Edit Pembayaran
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_pembayaran" id="edit_id_pembayaran">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    ID Pembayaran
                                </label>
                                <input type="text" id="display_id_pembayaran" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Status
                                </label>
                                <select name="status" id="edit_status" class="form-control" required>
                                    <option value="Belum Lunas">⚠️ Belum Lunas</option>
                                    <option value="Sudah Lunas">✅ Sudah Lunas</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    NISN
                                </label>
                                <input type="text" name="nisn" id="edit_nisn" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    ID SPP
                                </label>
                                <input type="text" name="id_spp" id="edit_id_spp" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Tgl Bayar
                                </label>
                                <input type="date" name="tgl_bayar" id="edit_tgl_bayar" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Tgl Terakhir Bayar
                                </label>
                                <input type="date" name="tgl_terakhir_bayar" id="edit_tgl_terakhir_bayar" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Batas Pembayaran
                                </label>
                                <input type="date" name="batas_pembayaran" id="edit_batas_pembayaran" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Jumlah Bulan
                                </label>
                                <input type="number" name="jumlah_bulan" id="edit_jumlah_bulan" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Nominal Bayar
                                </label>
                                <input type="text" name="nominal_bayar" id="edit_nominal_bayar" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Jumlah Bayar
                                </label>
                                <input type="number" name="jumlah_bayar" id="edit_jumlah_bayar" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-undo-alt text-primary me-1"></i>Kembalian
                            </label>
                            <input type="text" name="kembalian" id="edit_kembalian" class="form-control" readonly>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" name="edit" class="btn btn-primary">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set default date
        const today = new Date().toISOString().split('T')[0];
        
        // Auto-fill data when NISN is entered
        let timeoutId;
        document.getElementById('nisn').addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                const nisn = this.value;
                if (nisn.length >= 3) {
                    fetchDataSiswa(nisn);
                }
            }, 500);
        });
        
        function fetchDataSiswa(nisn) {
            fetch('pembayaran.php?get_data=true&nisn=' + nisn)
                .then(response => response.json())
                .then(data => {
                    if (data.nama) {
                        // Tampilkan info siswa
                        document.getElementById('nama_siswa').innerText = data.nama;
                        document.getElementById('display_id_spp').innerText = data.id_spp;
                        document.getElementById('infoSiswa').classList.add('show');
                        
                        // Isi data otomatis
                        document.getElementById('id_spp').value = data.id_spp;
                        document.getElementById('id_pembayaran').value = data.id_pembayaran;
                        document.getElementById('nominal_per_bulan').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.nominal);
                        document.getElementById('nominal_bayar_hidden').value = data.nominal;
                        
                        // Hitung total
                        hitungTotalBayar();
                    } else {
                        document.getElementById('infoSiswa').classList.remove('show');
                        document.getElementById('id_spp').value = '';
                        document.getElementById('nominal_per_bulan').value = '';
                        document.getElementById('nominal_bayar_hidden').value = '';
                        alert('NISN tidak ditemukan!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data');
                });
        }
        
        // Hitung total yang harus dibayar
        function hitungTotalBayar() {
            const nominalPerBulan = parseFloat(document.getElementById('nominal_bayar_hidden').value) || 0;
            const jumlahBulan = parseInt(document.getElementById('jumlah_bulan').value) || 0;
            const total = nominalPerBulan * jumlahBulan;
            document.getElementById('total_harus_bayar').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            document.getElementById('nominal_bayar_hidden').value = total;
            hitungKembalian();
        }
        
        // Hitung kembalian
        function hitungKembalian() {
            const totalBayar = parseFloat(document.getElementById('nominal_bayar_hidden').value) || 0;
            const jumlahBayar = parseFloat(document.getElementById('jumlah_bayar').value) || 0;
            const kembalian = jumlahBayar - totalBayar;
            
            if (kembalian >= 0) {
                document.getElementById('kembalian').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(kembalian);
            } else {
                document.getElementById('kembalian').value = 'Rp 0 (Kurang Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(kembalian)) + ')';
            }
        }
        
        // Event listeners untuk perhitungan otomatis
        document.getElementById('jumlah_bulan').addEventListener('input', hitungTotalBayar);
        document.getElementById('jumlah_bayar').addEventListener('input', hitungKembalian);
        
        // Open add modal
        function openAddModal() {
            document.getElementById('formPembayaran').reset();
            document.getElementById('infoSiswa').classList.remove('show');
            document.getElementById('dateFields').classList.remove('show');
            document.getElementById('id_pembayaran').value = '';
            document.getElementById('nisn').focus();
            new bootstrap.Modal(document.getElementById('addModal')).show();
        }
        
        // Toggle date fields and jumlah bayar based on status
        function toggleDateFields() {
            const status = document.getElementById('status').value;
            const dateFields = document.getElementById('dateFields');
            const jumlahBayar = document.getElementById('jumlah_bayar');
            const tglBayar = document.getElementById('tgl_bayar');
            const tglTerakhir = document.getElementById('tgl_terakhir_bayar');
            const batasPembayaran = document.getElementById('batas_pembayaran');
            
            if (status === 'Sudah Lunas') {
                dateFields.classList.add('show');
                tglBayar.required = true;
                tglTerakhir.required = true;
                batasPembayaran.required = true;
                jumlahBayar.required = true;
                
                // Set default dates
                if (!tglBayar.value) tglBayar.value = today;
                if (!tglTerakhir.value) tglTerakhir.value = today;
                if (!batasPembayaran.value) batasPembayaran.value = today;
            } else {
                dateFields.classList.remove('show');
                tglBayar.required = false;
                tglTerakhir.required = false;
                batasPembayaran.required = false;
                jumlahBayar.required = false;
                jumlahBayar.value = '';
                document.getElementById('kembalian').value = '';
            }
        }
        
        function editPembayaran(id, status, nisn, tgl_bayar, tgl_terakhir, batas, bulan, id_spp, nominal, bayar, kembalian) {
            document.getElementById('edit_id_pembayaran').value = id;
            document.getElementById('display_id_pembayaran').value = id;
            document.getElementById('edit_status').value = status;
            document.getElementById('edit_nisn').value = nisn;
            document.getElementById('edit_tgl_bayar').value = tgl_bayar;
            document.getElementById('edit_tgl_terakhir_bayar').value = tgl_terakhir;
            document.getElementById('edit_batas_pembayaran').value = batas;
            document.getElementById('edit_jumlah_bulan').value = bulan;
            document.getElementById('edit_id_spp').value = id_spp;
            document.getElementById('edit_nominal_bayar').value = nominal;
            document.getElementById('edit_jumlah_bayar').value = bayar;
            document.getElementById('edit_kembalian').value = kembalian;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        function searchData() {
            const searchValue = document.getElementById('searchInput').value;
            window.location.href = 'pembayaran.php?search=' + encodeURIComponent(searchValue);
        }
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchData();
            }
        });
        
        // Edit modal perhitungan kembalian
        document.getElementById('edit_jumlah_bayar').addEventListener('input', function() {
            const nominal = parseFloat(document.getElementById('edit_nominal_bayar').value) || 0;
            const bayar = parseFloat(this.value) || 0;
            const kembalian = bayar - nominal;
            document.getElementById('edit_kembalian').value = kembalian >= 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(kembalian) : 'Rp 0';
        });
        
        // Animasi fade-in untuk card
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .card-dashboard');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>