<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'config/koneksi.php';

if (isset($_POST['tambah'])) {
    $id_petugas = $_POST['id_petugas'];
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $nama_petugas = $_POST['nama_petugas'];
    $level = $_POST['level'];
    
    $query = "INSERT INTO tb_petugas VALUES ('$id_petugas', '$username', '$password', '$nama_petugas', '$level')";
    mysqli_query($conn, $query);
    header("Location: petugas.php");
}

if (isset($_POST['edit'])) {
    $id_petugas = $_POST['id_petugas'];
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $nama_petugas = $_POST['nama_petugas'];
    $level = $_POST['level'];
    
    $query = "UPDATE tb_petugas SET username='$username', password='$password', nama_petugas='$nama_petugas', level='$level' WHERE id_petugas='$id_petugas'";
    mysqli_query($conn, $query);
    header("Location: petugas.php");
}

// Get data for edit
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $query = "SELECT * FROM tb_petugas WHERE id_petugas='$id'";
    $result = mysqli_query($conn, $query);
    $edit_data = mysqli_fetch_assoc($result);
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM tb_petugas WHERE id_petugas='$id'");
    header("Location: petugas.php");
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $query = "SELECT * FROM tb_petugas WHERE username LIKE '%$search%' OR nama_petugas LIKE '%$search%' OR id_petugas LIKE '%$search%' ORDER BY level ASC";
} else {
    $query = "SELECT * FROM tb_petugas ORDER BY level ASC";
}

// Hitung total data
$total_data = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_petugas"));
$total_admin = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_petugas WHERE level='admin'"));
$total_petugas = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_petugas WHERE level='petugas'"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Petugas - SPP Siswa</title>
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
        
        .badge-petugas {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .badge-admin {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
            color: white;
        }
        
        .badge-role {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        
        .role-admin {
            background: #dc3545;
            color: white;
        }
        
        .role-petugas {
            background: #28a745;
            color: white;
        }
        
        .role-siswa {
            background: #17a2b8;
            color: white;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate {
            animation: fadeIn 0.5s ease-out;
        }
        
        .password-field {
            font-family: monospace;
            letter-spacing: 1px;
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
                    <a class="nav-link active" href="petugas.php"><i class="fas fa-users me-2"></i>Data Petugas</a>
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
                            <i class="fas fa-users me-2 text-primary"></i>
                            Data Petugas
                        </h2>
                        <p class="text-muted mb-0">Kelola data petugas dan administrator sistem</p>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <?php echo date('l, d F Y'); ?>
                    </div>
                </div>
                
                <!-- Tombol Tambah -->
                <div class="mb-4 animate">
                    <button class="btn btn-tambah text-white" onclick="openAddModal()">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Petugas Baru
                    </button>
                </div>
                
                <!-- Tabel Data Petugas -->
                <div class="card-dashboard animate">
                    <div class="card-header-custom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i>
                                Daftar Petugas
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
                                               placeholder="Cari petugas berdasarkan ID, Username, atau Nama..." 
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
                                        <th width="15%">ID Petugas</th>
                                        <th width="20%">Username</th>
                                        <th width="20%">Password</th>
                                        <th width="25%">Nama Petugas</th>
                                        <th width="10%">Level</th>
                                        <th width="10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = mysqli_query($conn, $query);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $roleClass = '';
                                            $roleIcon = '';
                                            if ($row['level'] == 'admin') {
                                                $roleClass = 'role-admin';
                                                $roleIcon = 'fa-shield-alt';
                                            } elseif ($row['level'] == 'petugas') {
                                                $roleClass = 'role-petugas';
                                                $roleIcon = 'fa-user-tie';
                                            } else {
                                                $roleClass = 'role-siswa';
                                                $roleIcon = 'fa-user-graduate';
                                            }
                                    ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $row['id_petugas']; ?></td>
                                        <td>
                                            <?php echo $row['username']; ?>
                                        </td>
                                        <td class="password-field">
                                            ••••••••
                                        </td>
                                        <td>
                                            <?php echo $row['nama_petugas']; ?>
                                        </td>
                                        <td>
                                            <span class="badge-role <?php echo $roleClass; ?>">
                                                <?php echo strtoupper($row['level']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button onclick="editPetugas('<?php echo $row['id_petugas']; ?>', '<?php echo $row['username']; ?>', '', '<?php echo $row['nama_petugas']; ?>', '<?php echo $row['level']; ?>')" 
                                                    class="btn btn-warning btn-action" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (!isset($_SESSION['id_petugas']) || $row['id_petugas'] != $_SESSION['id_petugas']): ?>
                                            <a href="petugas.php?hapus=<?php echo $row['id_petugas']; ?>" 
                                               class="btn btn-danger btn-action" 
                                               onclick="return confirm('Yakin ingin menghapus data petugas ini?')" 
                                               title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php else: ?>
                                            <button class="btn btn-secondary btn-action" disabled title="Tidak dapat menghapus akun sendiri">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <h6 class="text-muted">Belum ada data petugas</h6>
                                            <p class="text-muted small">Klik tombol "Tambah Petugas Baru" untuk menambahkan data</p>
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header-custom">
                    <h5 class="modal-title">
                        Tambah Petugas Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                ID Petugas
                            </label>
                            <input type="text" name="id_petugas" class="form-control form-control-lg" 
                                   placeholder="Contoh: PGT-001" required>
                            <small class="text-muted">Masukkan ID Petugas yang unik</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Username
                            </label>
                            <input type="text" name="username" class="form-control form-control-lg" 
                                   placeholder="Masukkan Username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Password
                            </label>
                            <input type="password" name="password" class="form-control form-control-lg" 
                                   placeholder="Masukkan Password" required>
                            <small class="text-muted">Password akan dienkripsi secara otomatis</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Nama Petugas
                            </label>
                            <input type="text" name="nama_petugas" class="form-control form-control-lg" 
                                   placeholder="Masukkan Nama Lengkap" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Level
                            </label>
                            <select name="level" class="form-control form-control-lg" required>
                                <option value="">Pilih Level</option>
                                <option value="admin">👑 Administrator</option>
                                <option value="petugas">👔 Petugas</option>
                            </select>
                            <small class="text-muted">Level menentukan hak akses pengguna</small>
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
                        Edit Petugas
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_petugas" id="edit_id_petugas">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                ID Petugas
                            </label>
                            <input type="text" id="display_id_petugas" class="form-control form-control-lg" readonly>
                            <small class="text-muted">ID Petugas tidak dapat diubah</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Username
                            </label>
                            <input type="text" name="username" id="edit_username" class="form-control form-control-lg" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Password
                            </label>
                            <input type="password" name="password" id="edit_password" class="form-control form-control-lg" 
                                   placeholder="Kosongkan jika tidak ingin mengubah password">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Nama Petugas
                            </label>
                            <input type="text" name="nama_petugas" id="edit_nama_petugas" class="form-control form-control-lg" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Level
                            </label>
                            <select name="level" id="edit_level" class="form-control form-control-lg" required>
                                <option value="admin">👑 Administrator</option>
                                <option value="petugas">👔 Petugas</option>
                            </select>
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
        function editPetugas(id, username, password, nama, level) {
            document.getElementById('edit_id_petugas').value = id;
            document.getElementById('display_id_petugas').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_nama_petugas').value = nama;
            document.getElementById('edit_level').value = level;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        function searchData() {
            const searchValue = document.getElementById('searchInput').value;
            window.location.href = 'petugas.php?search=' + encodeURIComponent(searchValue);
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
        });
    </script>
</body>
</html>