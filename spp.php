<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'config/koneksi.php';

if (isset($_POST['tambah'])) {
    $id_spp = $_POST['id_spp'];
    $tahun = $_POST['tahun'];
    $nominal = str_replace('.', '', $_POST['nominal']);
    
    $query = "INSERT INTO tb_spp VALUES ('$id_spp', '$tahun', '$nominal')";
    mysqli_query($conn, $query);
    header("Location: spp.php");
}

if (isset($_POST['edit'])) {
    $id_spp = $_POST['id_spp'];
    $tahun = $_POST['tahun'];
    $nominal = str_replace('.', '', $_POST['nominal']);
    
    $query = "UPDATE tb_spp SET tahun='$tahun', nominal='$nominal' WHERE id_spp='$id_spp'";
    mysqli_query($conn, $query);
    header("Location: spp.php");
}

// Get data for edit
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $query = "SELECT * FROM tb_spp WHERE id_spp='$id'";
    $result = mysqli_query($conn, $query);
    $edit_data = mysqli_fetch_assoc($result);
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM tb_spp WHERE id_spp='$id'");
    header("Location: spp.php");
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $query = "SELECT * FROM tb_spp WHERE id_spp LIKE '%$search%' OR tahun LIKE '%$search%' OR nominal LIKE '%$search%' ORDER BY tahun DESC";
} else {
    $query = "SELECT * FROM tb_spp ORDER BY tahun DESC";
}

// Hitung total data
$total_data = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_spp"));

// Hitung total nominal
$query_total = mysqli_query($conn, "SELECT SUM(nominal) as total_nominal FROM tb_spp");
$total_nominal = mysqli_fetch_assoc($query_total)['total_nominal'];
$total_nominal = $total_nominal ? $total_nominal : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data SPP - SPP Siswa</title>
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
        
        .badge-spp {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .nominal-text {
            font-weight: bold;
            color: #28a745;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate {
            animation: fadeIn 0.5s ease-out;
        }
        
        .format-money {
            font-family: monospace;
            font-size: 14px;
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
                    <a class="nav-link active" href="spp.php"><i class="fas fa-money-bill me-2"></i>Data SPP</a>
                    <a class="nav-link" href="petugas.php"><i class="fas fa-users me-2"></i>Data Petugas</a>
                    <a class="nav-link" href="siswa.php"><i class="fas fa-user-graduate me-2"></i>Data Siswa</a>
                    <a class="nav-link" href="pembayaran.php"><i class="fas fa-credit-card me-2"></i>Pembayaran</a>
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
                            <i class="fas fa-money-bill-wave me-2 text-primary"></i>
                            Data SPP
                        </h2>
                        <p class="text-muted mb-0">Kelola data SPP dan nominal pembayaran</p>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <?php echo date('l, d F Y'); ?>
                    </div>
                </div>
                
                <!-- Tombol Tambah -->
                <div class="mb-4 animate">
                    <button class="btn btn-tambah text-white" onclick="openAddModal()">
                        <i class="fas fa-plus-circle me-2"></i>Tambah SPP Baru
                    </button>
                </div>
                
                <!-- Tabel Data SPP -->
                <div class="card-dashboard animate">
                    <div class="card-header-custom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i>
                                Daftar SPP
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
                                               placeholder="Cari SPP berdasarkan ID, Tahun, atau Nominal..." 
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
                                        <th width="20%">ID SPP</th>
                                        <th width="30%">Tahun</th>
                                        <th width="35%">Nominal</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = mysqli_query($conn, $query);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $row['id_spp']; ?></td>
                                        <td>
                                            <?php echo $row['tahun']; ?>
                                        </td>
                                        <td class="nominal-text">
                                            Rp <?php echo number_format($row['nominal'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-center">
                                            <button onclick="editSPP('<?php echo $row['id_spp']; ?>', '<?php echo $row['tahun']; ?>', '<?php echo $row['nominal']; ?>')" 
                                                    class="btn btn-warning btn-action" title="Edit">
                                                Edit
                                            </button>
                                            <a href="spp.php?hapus=<?php echo $row['id_spp']; ?>" 
                                               class="btn btn-danger btn-action" 
                                               onclick="return confirm('Yakin ingin menghapus data SPP ini?')" 
                                               title="Hapus">
                                                Hapus
                                            </a>
                                         </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <h6 class="text-muted">Belum ada data SPP</h6>
                                            <p class="text-muted small">Klik tombol "Tambah SPP Baru" untuk menambahkan data</p>
                                         </td>
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header-custom">
                    <h5 class="modal-title">
                        Tambah SPP Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" id="formTambah">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                ID SPP
                            </label>
                            <input type="text" name="id_spp" class="form-control form-control-lg" 
                                   placeholder="Contoh: SPP-2024" required>
                            <small class="text-muted">Masukkan ID SPP yang unik</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Tahun
                            </label>
                            <input type="number" name="tahun" class="form-control form-control-lg" 
                                   placeholder="Contoh: 2024" value="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Nominal
                            </label>
                            <input type="text" name="nominal" id="nominal_tambah" class="form-control form-control-lg format-money" 
                                   placeholder="Contoh: 1000000" required>
                            <small class="text-muted">Masukkan nominal dalam angka (contoh: 1000000)</small>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Batal
                            </button>
                            <button type="submit" name="tambah" class="btn btn-primary">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header-custom">
                    <h5 class="modal-title">
                        Edit SPP
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_spp" id="edit_id_spp">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                ID SPP
                            </label>
                            <input type="text" id="display_id_spp" class="form-control form-control-lg" readonly>
                            <small class="text-muted">ID SPP tidak dapat diubah</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Tahun
                            </label>
                            <input type="number" name="tahun" id="edit_tahun" class="form-control form-control-lg" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Nominal
                            </label>
                            <input type="text" name="nominal" id="edit_nominal" class="form-control form-control-lg format-money" required>
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
        function editSPP(id, tahun, nominal) {
            document.getElementById('edit_id_spp').value = id;
            document.getElementById('display_id_spp').value = id;
            document.getElementById('edit_tahun').value = tahun;
            document.getElementById('edit_nominal').value = formatNumberInput(nominal);
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        function searchData() {
            const searchValue = document.getElementById('searchInput').value;
            window.location.href = 'spp.php?search=' + encodeURIComponent(searchValue);
        }
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchData();
            }
        });
        
        function openAddModal() {
            new bootstrap.Modal(document.getElementById('addModal')).show();
        }
        
        // Format number dengan titik sebagai pemisah ribuan
        function formatNumberInput(value) {
            if (!value) return '';
            let number = value.toString().replace(/\./g, '');
            return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        // Format angka pada input
        function formatInputNumber(input) {
            let value = input.value.replace(/\./g, '');
            if (value) {
                input.value = formatNumberInput(value);
            }
        }
        
        // Event listener untuk format nominal
        document.getElementById('nominal_tambah')?.addEventListener('input', function() {
            formatInputNumber(this);
        });
        
        document.getElementById('edit_nominal')?.addEventListener('input', function() {
            formatInputNumber(this);
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