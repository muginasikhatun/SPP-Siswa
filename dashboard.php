<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
include 'config/koneksi.php';

// Hitung persentase kelulusan pembayaran
$total_siswa = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_siswa"));
$total_lunas = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT nisn FROM tb_pembayaran WHERE status='sudah lunas'"));
$persentase_lunas = $total_siswa > 0 ? round(($total_lunas / $total_siswa) * 100, 1) : 0;

// Data pembayaran terbaru
$query_terbaru = "SELECT p.*, s.nama FROM tb_pembayaran p 
                  JOIN tb_siswa s ON p.nisn = s.nisn 
                  ORDER BY p.tgl_bayar DESC LIMIT 5";
$pembayaran_terbaru = mysqli_query($conn, $query_terbaru);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SPP Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        .card-stat {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
            overflow: hidden;
            position: relative;
        }
        
        .card-stat:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .card-stat .icon {
            position: absolute;
            right: 20px;
            bottom: 20px;
            font-size: 60px;
            opacity: 0.2;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-success {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }
        
        .gradient-warning {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }
        
        .gradient-info {
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        }
        
        .gradient-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .card-dashboard {
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .card-dashboard:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .table-custom {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .table-custom thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .welcome-text {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 5px solid #667eea;
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
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate {
            animation: fadeInUp 0.6s ease-out;
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
                    <a class="nav-link active" href="dashboard.php"><i class="fas fa-home me-2"></i>Dashboard</a>
                    <a class="nav-link" href="kelas.php"><i class="fas fa-school me-2"></i>Data Kelas</a>
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
                <!-- Welcome Section -->
                <div class="welcome-text animate">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">
                                <i class="fas fa-chart-line text-primary me-2"></i>
                                Dashboard SPP
                            </h3>
                            <p class="text-muted mb-0">Selamat datang di sistem pembayaran SPP online</p>
                        </div>
                        <div class="text-end">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo date('l, d F Y'); ?>
                            </small>
                        </div>
                    </div>
                </div>
                
                <?php
                // Query data statistik
                $kelas = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_kelas"));
                $spp = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_spp"));
                $petugas = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_petugas"));
                $siswa = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_siswa"));
                $pembayaran = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_pembayaran"));
                $sudah_lunas = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT nisn FROM tb_pembayaran WHERE status='sudah lunas'"));
                $belum_lunas = $total_siswa - $sudah_lunas;
                ?>
                
                <!-- Statistik Cards -->
                <div class="row mb-4 animate">
                    <div class="col-md-3 mb-3">
                        <div class="card-stat gradient-primary text-white p-3">
                            <div class="icon">
                                <i class="fas fa-school"></i>
                            </div>
                            <div class="stat-number"><?php echo $kelas; ?></div>
                            <p class="stat-label">Total Kelas</p>
                            <small><i class="fas fa-building me-1"></i> Data kelas aktif</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card-stat gradient-success text-dark p-3">
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-number"><?php echo $spp; ?></div>
                            <p class="stat-label">Total SPP</p>
                            <small><i class="fas fa-tags me-1"></i> Jenjang SPP</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card-stat gradient-warning text-dark p-3">
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-number"><?php echo $petugas; ?></div>
                            <p class="stat-label">Total Petugas</p>
                            <small><i class="fas fa-user-tie me-1"></i> Pengelola sistem</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card-stat gradient-info text-dark p-3">
                            <div class="icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-number"><?php echo $siswa; ?></div>
                            <p class="stat-label">Total Siswa</p>
                            <small><i class="fas fa-users me-1"></i> Siswa terdaftar</small>
                        </div>
                    </div>
                </div>
                
                <!-- Row 2 -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card-dashboard bg-white p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-pie text-primary me-2"></i>
                                    Statistik Pembayaran
                                </h5>
                                <i class="fas fa-chart-line text-muted"></i>
                            </div>
                            <div class="text-center mb-3">
                                <div class="position-relative d-inline-block">
                                    <canvas id="paymentChart" width="200" height="200"></canvas>
                                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                                        <h3 class="mb-0"><?php echo $persentase_lunas; ?>%</h3>
                                        <small class="text-muted">Lunas</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center mt-3">
                                <div class="col-6">
                                    <div class="bg-success bg-opacity-10 rounded p-2">
                                        <small class="text-muted">Lunas</small>
                                        <h5 class="mb-0 text-success"><?php echo $sudah_lunas; ?></h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-danger bg-opacity-10 rounded p-2">
                                        <small class="text-muted">Belum Lunas</small>
                                        <h5 class="mb-0 text-danger"><?php echo $belum_lunas; ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8 mb-3">
                        <div class="card-dashboard bg-white p-3">
                            <h5 class="mb-3">
                                <i class="fas fa-clock text-warning me-2"></i>
                                Pembayaran Terbaru
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Siswa</th>
                                            <th>Nominal</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($pembayaran_terbaru)): ?>
                                        <tr>
                                            <td><small><?php echo $row['tgl_bayar'] ? date('d/m/Y', strtotime($row['tgl_bayar'])) : '-'; ?></small></td>
                                            <td><small><?php echo $row['nama']; ?></small></td>
                                            <td><small class="text-success">Rp <?php echo number_format($row['nominal_bayar'], 0, ',', '.'); ?></small></td>
                                            <td><small class="<?php echo $row['status'] == 'Sudah Lunas' ? 'text-success' : 'text-warning'; ?>"><?php echo $row['status']; ?></small></td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <?php if (mysqli_num_rows($pembayaran_terbaru) == 0): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                                Belum ada pembayaran
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Donut Chart untuk status pembayaran
        const ctx1 = document.getElementById('paymentChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Lunas', 'Belum Lunas'],
                datasets: [{
                    data: [<?php echo $sudah_lunas; ?>, <?php echo $belum_lunas; ?>],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = <?php echo $sudah_lunas + $belum_lunas; ?>;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // Animasi fade-in untuk card
        const cards = document.querySelectorAll('.card-stat, .card-dashboard');
        cards.forEach((card, index) => {
            card.style.animation = `fadeInUp 0.6s ease-out ${index * 0.1}s forwards`;
            card.style.opacity = '0';
            card.style.animationFillMode = 'forwards';
        });
    </script>
</body>
</html>