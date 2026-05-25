<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'config/koneksi.php';

// Handle AJAX request for getting payment data based on NISN or Nama
if (isset($_GET['get_data']) && $_GET['get_data'] == 'true' && isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    
    // Ambil data pembayaran siswa berdasarkan NISN atau Nama
    $query = "SELECT
                s.nisn,
                s.nama,
                s.no_telp,
                s.alamat,
                k.nama_kelas,
                sp.nominal as nominal_spp,
                sp.id_spp,
                SUM(p.nominal_bayar) as total_dibayar,
                SUM(p.jumlah_bulan) as total_bulan,
                MAX(p.tgl_bayar) as tgl_bayar_terakhir,
                MAX(p.tgl_terakhir_bayar) as tgl_terakhir_bayar,
                MAX(p.batas_pembayaran) as batas_pembayaran,
                COUNT(p.id_pembayaran) as jumlah_pembayaran
              FROM tb_siswa s
              LEFT JOIN tb_kelas k ON s.id_kelas = k.id_kelas
              LEFT JOIN tb_spp sp ON s.id_spp = sp.id_spp
              LEFT JOIN tb_pembayaran p ON s.nisn = p.nisn
              WHERE s.nisn = '$search' OR s.nama LIKE '%$search%'
              GROUP BY s.nisn";
    
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Hitung status berdasarkan data pembayaran
        $total_bulan = $row['total_bulan'] ? $row['total_bulan'] : 0;
        $total_dibayar = $row['total_dibayar'] ? $row['total_dibayar'] : 0;
        $nominal_spp = $row['nominal_spp'] ? $row['nominal_spp'] : 0;
        
        // Tentukan status
        if ($total_dibayar >= $nominal_spp * 12) {
            $status = 'LUNAS (Full)';
            $status_class = 'success';
        } elseif ($total_dibayar > 0) {
            $status = 'BELUM LUNAS (Sebagian)';
            $status_class = 'warning';
        } else {
            $status = 'BELUM ADA PEMBAYARAN';
            $status_class = 'danger';
        }
        
        // Ambil detail pembayaran per bulan
        $nisn_result = $row['nisn'];
        $detail_query = "SELECT id_pembayaran, nisn, tgl_bayar, jumlah_bulan, nominal_bayar, status, tgl_terakhir_bayar, batas_pembayaran, jumlah_bayar, kembalian, id_spp
                         FROM tb_pembayaran
                         WHERE nisn = '$nisn_result'
                         ORDER BY tgl_bayar DESC";
        $detail_result = mysqli_query($conn, $detail_query);
        $detail_pembayaran = [];
        while ($detail = mysqli_fetch_assoc($detail_result)) {
            $detail_pembayaran[] = $detail;
        }
        
        echo json_encode([
            'found' => true,
            'nisn' => $row['nisn'],
            'nama' => $row['nama'],
            'no_telp' => $row['no_telp'] ? $row['no_telp'] : '-',
            'alamat' => $row['alamat'] ? $row['alamat'] : '-',
            'kelas' => $row['nama_kelas'] ? $row['nama_kelas'] : '-',
            'id_spp' => $row['id_spp'] ? $row['id_spp'] : '-',
            'nominal_spp' => number_format($nominal_spp, 0, ',', '.'),
            'total_bulan' => $total_bulan,
            'total_dibayar' => number_format($total_dibayar, 0, ',', '.'),
            'sisa_bayar' => number_format(($nominal_spp * 12) - $total_dibayar, 0, ',', '.'),
            'status' => $status,
            'status_class' => $status_class,
            'tgl_bayar_terakhir' => $row['tgl_bayar_terakhir'] ? date('d/m/Y', strtotime($row['tgl_bayar_terakhir'])) : '-',
            'tgl_terakhir_bayar' => $row['tgl_terakhir_bayar'] ? date('d/m/Y', strtotime($row['tgl_terakhir_bayar'])) : '-',
            'batas_pembayaran' => $row['batas_pembayaran'] ? date('d/m/Y', strtotime($row['batas_pembayaran'])) : '-',
            'jumlah_pembayaran' => $row['jumlah_pembayaran'],
            'detail_pembayaran' => $detail_pembayaran
        ]);
    } else {
        echo json_encode(['found' => false]);
    }
    exit();
}

// Fetch all payment records for default display
$query_all = "SELECT p.*, s.nama 
              FROM tb_pembayaran p 
              JOIN tb_siswa s ON p.nisn = s.nisn 
              ORDER BY p.tgl_bayar DESC";
$result_all = mysqli_query($conn, $query_all);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Pembayaran - SPP Siswa</title>
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
        
        .info-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .info-box h6 {
            color: white;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .info-box table td {
            color: rgba(255,255,255,0.9);
        }
        
        .result-card {
            display: none;
        }
        
        .result-card.show {
            display: block;
            animation: fadeIn 0.5s ease-out;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 50px;
        }
        
        .loading.show {
            display: block;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .detail-table {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .detail-table h6 {
            color: #667eea;
            font-weight: bold;
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
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
                    <a class="nav-link" href="pembayaran.php"><i class="fas fa-credit-card me-2"></i>Pembayaran</a>
                    <a class="nav-link active" href="cek_pembayaran.php"><i class="fas fa-search me-2"></i>Cek Pembayaran</a>
                    <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </nav>
            </div>
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 animate">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-search me-2 text-primary"></i>
                            Cek Status Pembayaran SPP
                        </h2>
                        <p class="text-muted mb-0">Cek status pembayaran siswa berdasarkan NISN</p>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <?php echo date('l, d F Y'); ?>
                    </div>
                </div>
                
                <!-- Form Pencarian -->
                <div class="card-dashboard animate">
                    <div class="card-header-custom">
                        <h5 class="mb-0">
                            <i class="fas fa-search me-2"></i>
                            Pencarian Pembayaran
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="search-box">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-0">
                                            <i class="fas fa-search text-primary"></i>
                                        </span>
                                        <input type="text" id="search_input" class="form-control border-0"
                                               placeholder="Masukkan NISN atau Nama Siswa..." autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-cari text-white w-100" onclick="cekPembayaran()">
                                    <i class="fas fa-search me-2"></i> Cek
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div class="loading" id="loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Mencari data pembayaran...</p>
                </div>

                <!-- Detail Pembayaran - Default Display -->
                <div class="card-dashboard animate">
                    <div class="card-header-custom">
                        <h5 class="mb-0">
                            <i class="fas fa-list-alt me-2"></i>
                            Detail Riwayat Pembayaran
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                                <thead>
                                    <tr>
                                        <th>ID Pembayaran</th>
                                        <th>ID SPP</th>
                                        <th>NISN</th>
                                        <th>Nama Siswa</th>
                                        <th>Tgl Bayar</th>
                                        <th>Jumlah Bulan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="detail_pembayaran">
                                    <?php
                                    if (mysqli_num_rows($result_all) > 0) {
                                        while ($row = mysqli_fetch_assoc($result_all)) {
                                            $statusClass = $row['status'] == 'Sudah Lunas' ? 'text-success' : 'text-warning';
                                            $statusText = $row['status'] == 'Sudah Lunas' ? 'LUNAS' : 'BELUM LUNAS';
                                            $tgl_bayar = $row['tgl_bayar'] ? date('d/m/Y', strtotime($row['tgl_bayar'])) : '-';
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id_pembayaran']; ?></td>
                                        <td><?php echo $row['id_spp']; ?></td>
                                        <td><?php echo $row['nisn']; ?></td>
                                        <td><strong><?php echo $row['nama']; ?></strong></td>
                                        <td><?php echo $tgl_bayar; ?></td>
                                        <td><?php echo $row['jumlah_bulan']; ?> bulan</td>
                                        <td class="<?php echo $statusClass; ?> fw-bold"><?php echo $statusText; ?></td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <h6 class="text-muted">Belum ada riwayat pembayaran</h6>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Hasil Pencarian (Hidden by default) -->
                <div id="resultCard" class="result-card">
                    <div class="card-dashboard animate">
                        <div class="card-header-custom">
                            <h5 class="mb-0">
                                <i class="fas fa-user-graduate me-2"></i>
                                Informasi Pembayaran Siswa
                            </h5>
                        </div>
                        <div class="card-body p-4">
                    
                    <!-- Detail Pembayaran Search Result -->
                    <div class="detail-table">
                        <h6><i class="fas fa-list-alt me-2"></i>Hasil Pencarian</h6>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                                <thead>
                                    <tr>
                                        <th>ID Pembayaran</th>
                                        <th>ID SPP</th>
                                        <th>NISN</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>No. Telp</th>
                                        <th>Tgl Bayar</th>
                                        <th>Jumlah Bulan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="detail_pembayaran_search">
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <h6 class="text-muted">Belum ada riwayat pembayaran</h6>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Pesan Error -->
                <div id="errorMessage" class="alert alert-danger mt-4" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="errorText"></span>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cek pembayaran saat enter ditekan
        document.getElementById('search_input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                cekPembayaran();
            }
        });

        function cekPembayaran() {
            const search = document.getElementById('search_input').value.trim();

            if (!search) {
                showError('Masukkan NISN atau Nama terlebih dahulu!');
                return;
            }

            // Tampilkan loading
            document.getElementById('loading').classList.add('show');
            document.getElementById('resultCard').classList.remove('show');
            document.getElementById('errorMessage').style.display = 'none';

            // Fetch data
            fetch('cek_pembayaran.php?get_data=true&search=' + encodeURIComponent(search))
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loading').classList.remove('show');
                    
                    if (data.found) {
                        // Show search result card
                        document.getElementById('resultCard').classList.add('show');
                        
                        // Detail pembayaran search result
                        const detailBody = document.getElementById('detail_pembayaran_search');
                        if (data.detail_pembayaran && data.detail_pembayaran.length > 0) {
                            let html = '';
                            data.detail_pembayaran.forEach((item, index) => {
                                const statusClass = item.status === 'Sudah Lunas' ? 'text-success' : 'text-warning';
                                const statusText = item.status === 'Sudah Lunas' ? 'LUNAS' : 'BELUM LUNAS';
                                html += `
                                    <tr>
                                        <td>${item.id_pembayaran}</td>
                                        <td>${item.id_spp}</td>
                                        <td>${item.nisn}</td>
                                        <td><strong>${data.nama}</strong></td>
                                        <td>${data.kelas}</td>
                                        <td>${data.no_telp}</td>
                                        <td>${formatDate(item.tgl_bayar)}</td>
                                        <td>${item.jumlah_bulan} bulan</td>
                                        <td class="${statusClass} fw-bold">${statusText}</td>
                                    </tr>
                                `;
                            });
                            detailBody.innerHTML = html;
                        } else {
                            detailBody.innerHTML = '<tr><td colspan="9" class="text-center py-5"><i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i><h6 class="text-muted">Belum ada riwayat pembayaran</h6></td></tr>';
                        }
                        
                        // Tampilkan card hasil
                        document.getElementById('resultCard').classList.add('show');
                    } else {
                        showError(`Data dengan NISN atau Nama "${search}" tidak ditemukan! Pastikan data sudah terdaftar.`);
                    }
                })
                .catch(error => {
                    document.getElementById('loading').classList.remove('show');
                    console.error('Error:', error);
                    showError('Terjadi kesalahan saat menghubungi server. Silakan coba lagi.');
                });
        }
        
        function showError(message) {
            document.getElementById('errorText').innerText = message;
            document.getElementById('errorMessage').style.display = 'block';
            document.getElementById('resultCard').classList.remove('show');
        }
        
        function formatDate(dateString) {
            if (!dateString || dateString === '-' || dateString === '0000-00-00') return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID');
        }
        
        function formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }

        // Focus ke input saat halaman dimuat
        document.getElementById('search_input').focus();
    </script>
</body>
</html>
