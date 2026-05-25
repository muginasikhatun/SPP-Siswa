<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'config/koneksi.php';

// Error handling for database operations
if (isset($_POST['tambah'])) {
    $id_kelas = mysqli_real_escape_string($conn, $_POST['id_kelas']);
    $nama_kelas = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
    $komp_keahlian = mysqli_real_escape_string($conn, $_POST['komp_keahlian']);
    
    // Check if ID already exists
    $check_query = "SELECT * FROM tb_kelas WHERE id_kelas = '$id_kelas' OR nama_kelas = '$nama_kelas'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $existing = mysqli_fetch_assoc($check_result);
        if ($existing['id_kelas'] == $id_kelas) {
            $_SESSION['error'] = "ID Kelas '$id_kelas' sudah terdaftar!";
        } else {
            $_SESSION['error'] = "Nama Kelas '$nama_kelas' sudah terdaftar!";
        }
        header("Location: kelas.php");
        exit();
    } else {
        $query = "INSERT INTO tb_kelas (id_kelas, nama_kelas, komp_keahlian) VALUES ('$id_kelas', '$nama_kelas', '$komp_keahlian')";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Data kelas berhasil ditambahkan!";
        } else {
            $_SESSION['error'] = "Gagal menambahkan data: " . mysqli_error($conn);
        }
        header("Location: kelas.php");
        exit();
    }
}

if (isset($_POST['edit'])) {
    $id_kelas = mysqli_real_escape_string($conn, $_POST['id_kelas']);
    $nama_kelas = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
    $komp_keahlian = mysqli_real_escape_string($conn, $_POST['komp_keahlian']);
    
    // Check if new nama_kelas already exists for different ID
    $check_query = "SELECT * FROM tb_kelas WHERE nama_kelas = '$nama_kelas' AND id_kelas != '$id_kelas'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $_SESSION['error'] = "Nama Kelas '$nama_kelas' sudah digunakan oleh kelas lain!";
        header("Location: kelas.php");
        exit();
    } else {
        $query = "UPDATE tb_kelas SET nama_kelas='$nama_kelas', komp_keahlian='$komp_keahlian' WHERE id_kelas='$id_kelas'";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Data kelas berhasil diupdate!";
        } else {
            $_SESSION['error'] = "Gagal mengupdate data: " . mysqli_error($conn);
        }
        header("Location: kelas.php");
        exit();
    }
}

// Get data for edit
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $query = "SELECT * FROM tb_kelas WHERE id_kelas='$id'";
    $result = mysqli_query($conn, $query);
    $edit_data = mysqli_fetch_assoc($result);
}

if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    // Check if class has students
    $check_siswa = "SELECT * FROM tb_siswa WHERE id_kelas='$id'";
    $result_siswa = mysqli_query($conn, $check_siswa);
    
    if (mysqli_num_rows($result_siswa) > 0) {
        $_SESSION['error'] = "Tidak dapat menghapus kelas karena masih memiliki siswa!";
    } else {
        if (mysqli_query($conn, "DELETE FROM tb_kelas WHERE id_kelas='$id'")) {
            $_SESSION['success'] = "Data kelas berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus data: " . mysqli_error($conn);
        }
    }
    header("Location: kelas.php");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
if ($search) {
    $query = "SELECT * FROM tb_kelas WHERE nama_kelas LIKE '%$search%' OR komp_keahlian LIKE '%$search%' OR id_kelas LIKE '%$search%' ORDER BY id_kelas ASC";
} else {
    $query = "SELECT * FROM tb_kelas ORDER BY id_kelas ASC";
}

// Hitung total data
$total_data = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_kelas"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelas - SPP Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Your existing CSS styles remain the same */
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
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate {
            animation: fadeIn 0.5s ease-out;
        }
        
        .badge-kelas {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .alert-custom {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
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
                    <a class="nav-link active" href="kelas.php"><i class="fas fa-school me-2"></i>Data Kelas</a>
                    <a class="nav-link" href="spp.php"><i class="fas fa-money-bill me-2"></i>Data SPP</a>
                    <a class="nav-link" href="petugas.php"><i class="fas fa-users me-2"></i>Data Petugas</a>
                    <a class="nav-link" href="siswa.php"><i class="fas fa-user-graduate me-2"></i>Data Siswa</a>
                    <a class="nav-link" href="pembayaran.php"><i class="fas fa-credit-card me-2"></i>Pembayaran</a>
                    <a class="nav-link" href="cek_pembayaran.php"><i class="fas fa-search me-2"></i>Cek Pembayaran</a>
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- Display Alert Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show alert-custom animate" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show alert-custom animate" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 animate">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-school me-2 text-primary"></i>
                            Data Kelas
                        </h2>
                        <p class="text-muted mb-0">Kelola data kelas dan kompetensi keahlian</p>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <?php echo date('l, d F Y'); ?>
                    </div>
                </div>
                
                <!-- Tombol Tambah -->
                <div class="mb-4 animate">
                    <button class="btn btn-tambah text-white" onclick="openAddModal()">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Kelas Baru
                    </button>
                </div>
                
                <!-- Tabel Data Kelas -->
                <div class="card-dashboard animate">
                    <div class="card-header-custom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i>
                                Daftar Kelas
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
                                               placeholder="Cari kelas berdasarkan ID, Nama Kelas, atau Kompetensi Keahlian..." 
                                               value="<?php echo htmlspecialchars($search); ?>">
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
                                        <th width="15%">ID Kelas</th>
                                        <th width="30%">Nama Kelas</th>
                                        <th width="40%">Kompetensi Keahlian</th>
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
                                        <td class="fw-bold"><?php echo htmlspecialchars($row['id_kelas']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_kelas']); ?></td>
                                        <td>
                                            <i class="fas fa-code me-1 text-muted"></i>
                                            <?php echo htmlspecialchars($row['komp_keahlian']); ?>
                                         </td>
                                        <td class="text-center">
                                            <button onclick="editKelas('<?php echo htmlspecialchars($row['id_kelas']); ?>', '<?php echo htmlspecialchars($row['nama_kelas']); ?>', '<?php echo htmlspecialchars($row['komp_keahlian']); ?>')" 
                                                    class="btn btn-warning btn-action" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="kelas.php?hapus=<?php echo urlencode($row['id_kelas']); ?>" 
                                               class="btn btn-danger btn-action" 
                                               onclick="return confirm('Yakin ingin menghapus data kelas ini?\\n\\nPERINGATAN: Kelas tidak dapat dihapus jika masih memiliki siswa!')" 
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
                                        <td colspan="4" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <h6 class="text-muted">Belum ada data kelas</h6>
                                            <p class="text-muted small">Klik tombol "Tambah Kelas Baru" untuk menambahkan data</p>
                                         </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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
                        <i class="fas fa-plus-circle me-2"></i>
                        Tambah Kelas Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                ID Kelas <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="id_kelas" class="form-control form-control-lg" 
                                   placeholder="Contoh: X-RPL-1" required>
                            <small class="text-muted">Masukkan ID Kelas yang unik</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Nama Kelas <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_kelas" class="form-control form-control-lg" 
                                   placeholder="Contoh: X RPL 1" required>
                            <small class="text-muted">Nama kelas harus unik (tidak boleh sama)</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Kompetensi Keahlian <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="komp_keahlian" class="form-control form-control-lg" 
                                   placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Batal
                            </button>
                            <button type="submit" name="tambah" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan
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
                        <i class="fas fa-edit me-2"></i>
                        Edit Kelas
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_kelas" id="edit_id_kelas">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                ID Kelas
                            </label>
                            <input type="text" id="display_id_kelas" class="form-control form-control-lg" readonly>
                            <small class="text-muted">ID Kelas tidak dapat diubah</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Nama Kelas <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_kelas" id="edit_nama_kelas" class="form-control form-control-lg" required>
                            <small class="text-muted">Nama kelas harus unik (tidak boleh sama dengan kelas lain)</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Kompetensi Keahlian <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="komp_keahlian" id="edit_komp_keahlian" class="form-control form-control-lg" required>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Batal
                            </button>
                            <button type="submit" name="edit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editKelas(id, nama, komp) {
            document.getElementById('edit_id_kelas').value = id;
            document.getElementById('display_id_kelas').value = id;
            document.getElementById('edit_nama_kelas').value = nama;
            document.getElementById('edit_komp_keahlian').value = komp;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        function searchData() {
            const searchValue = document.getElementById('searchInput').value;
            window.location.href = 'kelas.php?search=' + encodeURIComponent(searchValue);
        }
        
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchData();
            }
        });
        
        function openAddModal() {
            new bootstrap.Modal(document.getElementById('addModal')).show();
        }
        
        // Animasi fade-in untuk card
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .card-dashboard');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Auto close alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>
