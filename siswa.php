<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'config/koneksi.php';

if (isset($_POST['tambah'])) {
    $nisn = $_POST['nisn'];
    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $id_kelas = $_POST['id_kelas'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    $id_spp = $_POST['id_spp'];
    
    // Ambil nama_kelas dari tb_kelas berdasarkan id_kelas
    $query_kelas = mysqli_query($conn, "SELECT nama_kelas FROM tb_kelas WHERE id_kelas='$id_kelas'");
    $kelas_data = mysqli_fetch_assoc($query_kelas);
    $nama_kelas = $kelas_data ? $kelas_data['nama_kelas'] : '';
    
    $query = "INSERT INTO tb_siswa (nisn, nis, nama, id_kelas, nama_kelas, alamat, no_telp, id_spp) 
              VALUES ('$nisn', '$nis', '$nama', '$id_kelas', '$nama_kelas', '$alamat', '$no_telp', '$id_spp')";
    mysqli_query($conn, $query);
    header("Location: siswa.php");
}

if (isset($_POST['edit'])) {
    $nisn = $_POST['nisn'];
    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $id_kelas = $_POST['id_kelas'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    $id_spp = $_POST['id_spp'];
    
    // Ambil nama_kelas dari tb_kelas berdasarkan id_kelas
    $query_kelas = mysqli_query($conn, "SELECT nama_kelas FROM tb_kelas WHERE id_kelas='$id_kelas'");
    $kelas_data = mysqli_fetch_assoc($query_kelas);
    $nama_kelas = $kelas_data ? $kelas_data['nama_kelas'] : '';
    
    $query = "UPDATE tb_siswa SET nis='$nis', nama='$nama', id_kelas='$id_kelas', nama_kelas='$nama_kelas', alamat='$alamat', no_telp='$no_telp', id_spp='$id_spp' WHERE nisn='$nisn'";
    mysqli_query($conn, $query);
    header("Location: siswa.php");
}

// Get data for edit
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $query = "SELECT * FROM tb_siswa WHERE nisn='$id'";
    $result = mysqli_query($conn, $query);
    $edit_data = mysqli_fetch_assoc($result);
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM tb_siswa WHERE nisn='$id'");
    header("Location: siswa.php");
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $query = "SELECT * FROM tb_siswa WHERE nama LIKE '%$search%' OR nisn LIKE '%$search%' OR nis LIKE '%$search%' OR nama_kelas LIKE '%$search%' ORDER BY nama ASC";
} else {
    $query = "SELECT * FROM tb_siswa ORDER BY nama ASC";
}

// Hitung total data
$total_data = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_siswa"));

// Ambil data kelas untuk dropdown
$kelas_list = mysqli_query($conn, "SELECT id_kelas, nama_kelas FROM tb_kelas ORDER BY id_kelas ASC");
$spp_list = mysqli_query($conn, "SELECT id_spp, tahun, nominal FROM tb_spp ORDER BY tahun DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - SPP Siswa</title>
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
        
        .badge-siswa {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate {
            animation: fadeIn 0.5s ease-out;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .alamat-cell {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                    <a class="nav-link active" href="siswa.php"><i class="fas fa-user-graduate me-2"></i>Data Siswa</a>
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
                            <i class="fas fa-user-graduate me-2 text-primary"></i>
                            Data Siswa
                        </h2>
                        <p class="text-muted mb-0">Kelola data siswa dan informasi akademik</p>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <?php echo date('l, d F Y'); ?>
                    </div>
                </div>
                
                <!-- Tombol Tambah -->
                <div class="mb-4 animate">
                    <button class="btn btn-tambah text-white" onclick="openAddModal()">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Siswa Baru
                    </button>
                </div>
                
                <!-- Tabel Data Siswa -->
                <div class="card-dashboard animate">
                    <div class="card-header-custom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2"></i>
                                Daftar Siswa
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
                                               placeholder="Cari siswa berdasarkan NISN, NIS, Nama, atau Kelas..." 
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
                                        <th width="10%">NISN</th>
                                        <th width="8%">NIS</th>
                                        <th width="15%">Nama</th>
                                        <th width="10%">Kelas</th>
                                        <th width="20%">Alamat</th>
                                        <th width="12%">No Telp</th>
                                        <th width="10%">ID SPP</th>
                                        <th width="10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = mysqli_query($conn, $query);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['nisn']; ?></td>
                                        <td><?php echo $row['nis']; ?></td>
                                        <td><strong><?php echo $row['nama']; ?></strong></td>
                                        <td>
                                            <?php echo $row['nama_kelas']; ?>
                                        </td>
                                        <td class="alamat-cell" title="<?php echo $row['alamat']; ?>">
                                            <?php echo $row['alamat']; ?>
                                        </td>
                                        <td>
                                            <?php echo $row['no_telp']; ?>
                                        </td>
                                        <td><?php echo $row['id_spp']; ?></td>
                                        <td class="text-center">
                                            <button onclick="editSiswa('<?php echo $row['nisn']; ?>', '<?php echo $row['nis']; ?>', '<?php echo $row['nama']; ?>', '<?php echo $row['id_kelas']; ?>', '<?php echo $row['alamat']; ?>', '<?php echo $row['no_telp']; ?>', '<?php echo $row['id_spp']; ?>')" 
                                                    class="btn btn-warning btn-action" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="siswa.php?hapus=<?php echo $row['nisn']; ?>" 
                                               class="btn btn-danger btn-action" 
                                               onclick="return confirm('Yakin ingin menghapus data siswa ini?')" 
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
                                            <h6 class="text-muted">Belum ada data siswa</h6>
                                            <p class="text-muted small">Klik tombol "Tambah Siswa Baru" untuk menambahkan data</p>
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
                        Tambah Siswa Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    NISN
                                </label>
                                <input type="text" name="nisn" class="form-control form-control-lg" 
                                       placeholder="Masukkan NISN" required>
                                <small class="text-muted">Nomor Induk Siswa Nasional</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    NIS
                                </label>
                                <input type="text" name="nis" class="form-control form-control-lg" 
                                       placeholder="Masukkan NIS" required>
                                <small class="text-muted">Nomor Induk Sekolah</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    Nama Lengkap
                                </label>
                                <input type="text" name="nama" class="form-control form-control-lg" 
                                       placeholder="Masukkan Nama Lengkap" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    Kelas
                                </label>
                                <select name="id_kelas" id="id_kelas_tambah" class="form-control form-control-lg" required>
                                    <option value="">Pilih Kelas</option>
                                    <?php 
                                    mysqli_data_seek($kelas_list, 0);
                                    while ($kelas = mysqli_fetch_assoc($kelas_list)): 
                                    ?>
                                    <option value="<?php echo $kelas['id_kelas']; ?>">
                                        <?php echo $kelas['id_kelas'] . ' - ' . $kelas['nama_kelas']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    Alamat
                                </label>
                                <textarea name="alamat" class="form-control" rows="3" 
                                          placeholder="Masukkan Alamat Lengkap" required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    No. Telepon
                                </label>
                                <input type="text" name="no_telp" class="form-control form-control-lg" 
                                       placeholder="Masukkan No. Telepon" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                SPP
                            </label>
                            <select name="id_spp" class="form-control form-control-lg" required>
                                <option value="">Pilih SPP</option>
                                <?php 
                                mysqli_data_seek($spp_list, 0);
                                while ($spp = mysqli_fetch_assoc($spp_list)): 
                                ?>
                                <option value="<?php echo $spp['id_spp']; ?>">
                                    <?php echo $spp['tahun'] . ' - Rp ' . number_format($spp['nominal'], 0, ',', '.'); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content modal-content-custom">
                <div class="modal-header-custom">
                    <h5 class="modal-title">
                        Edit Siswa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <input type="hidden" name="nisn" id="edit_nisn">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    NISN
                                </label>
                                <input type="text" id="display_nisn" class="form-control form-control-lg" readonly>
                                <small class="text-muted">NISN tidak dapat diubah</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    NIS
                                </label>
                                <input type="text" name="nis" id="edit_nis" class="form-control form-control-lg" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    Nama Lengkap
                                </label>
                                <input type="text" name="nama" id="edit_nama" class="form-control form-control-lg" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    Kelas
                                </label>
                                <select name="id_kelas" id="edit_id_kelas" class="form-control form-control-lg" required>
                                    <option value="">Pilih Kelas</option>
                                    <?php 
                                    $kelas_list2 = mysqli_query($conn, "SELECT id_kelas, nama_kelas FROM tb_kelas ORDER BY id_kelas ASC");
                                    while ($kelas = mysqli_fetch_assoc($kelas_list2)): 
                                    ?>
                                    <option value="<?php echo $kelas['id_kelas']; ?>">
                                        <?php echo $kelas['id_kelas'] . ' - ' . $kelas['nama_kelas']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    Alamat
                                </label>
                                <textarea name="alamat" id="edit_alamat" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    No. Telepon
                                </label>
                                <input type="text" name="no_telp" id="edit_no_telp" class="form-control form-control-lg" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                SPP
                            </label>
                            <select name="id_spp" id="edit_id_spp" class="form-control form-control-lg" required>
                                <option value="">Pilih SPP</option>
                                <?php 
                                $spp_list2 = mysqli_query($conn, "SELECT id_spp, tahun, nominal FROM tb_spp ORDER BY tahun DESC");
                                while ($spp = mysqli_fetch_assoc($spp_list2)): 
                                ?>
                                <option value="<?php echo $spp['id_spp']; ?>">
                                    <?php echo $spp['tahun'] . ' - Rp ' . number_format($spp['nominal'], 0, ',', '.'); ?>
                                </option>
                                <?php endwhile; ?>
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
        function editSiswa(nisn, nis, nama, id_kelas, alamat, no_telp, id_spp) {
            document.getElementById('edit_nisn').value = nisn;
            document.getElementById('display_nisn').value = nisn;
            document.getElementById('edit_nis').value = nis;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_id_kelas').value = id_kelas;
            document.getElementById('edit_alamat').value = alamat;
            document.getElementById('edit_no_telp').value = no_telp;
            document.getElementById('edit_id_spp').value = id_spp;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        
        function searchData() {
            const searchValue = document.getElementById('searchInput').value;
            window.location.href = 'siswa.php?search=' + encodeURIComponent(searchValue);
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