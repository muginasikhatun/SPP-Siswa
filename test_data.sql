-- Test Data for SPP Siswa Application
-- 5 rows for master data tables, 2 rows for transaction tables

USE db_spp;

-- Insert Data Kelas (5 rows)
INSERT INTO tb_kelas VALUES 
('KLS001', 'X RPL 1', 'Rekayasa Perangkat Lunak'),
('KLS002', 'X RPL 2', 'Rekayasa Perangkat Lunak'),
('KLS003', 'XI TKJ 1', 'Teknik Komputer Jaringan'),
('KLS004', 'XI TKJ 2', 'Teknik Komputer Jaringan'),
('KLS005', 'XII MM 1', 'Multimedia');

-- Insert Data SPP (5 rows)
INSERT INTO tb_spp VALUES 
('SPP001', 2024, '500000'),
('SPP002', 2024, '550000'),
('SPP003', 2024, '600000'),
('SPP004', 2024, '650000'),
('SPP005', 2024, '700000');

-- Insert Data Petugas (5 rows)
INSERT INTO tb_petugas VALUES 
('PTG001', 'admin', 'admin123', 'Administrator', 'admin'),
('PTG002', 'budi', 'budi123', 'Budi Santoso', 'petugas'),
('PTG003', 'siti', 'siti123', 'Siti Aminah', 'petugas'),
('PTG004', 'ahmad', 'ahmad123', 'Ahmad Fauzi', 'petugas'),
('PTG005', 'dewi', 'dewi123', 'Dewi Kartika', 'siswa');

-- Insert Data Siswa (5 rows)
INSERT INTO tb_siswa VALUES 
('1234567890', '12345678', 'Andi Pratama', 'KLS001', 'X RPL 1', 'Jl. Merdeka No. 10 Jakarta', '081234567890', 'SPP001'),
('1234567891', '12345679', 'Budi Setiawan', 'KLS002', 'X RPL 2', 'Jl. Sudirman No. 20 Bandung', '081234567891', 'SPP002'),
('1234567892', '12345680', 'Citra Lestari', 'KLS003', 'XI TKJ 1', 'Jl. Gatot Subroto No. 30 Surabaya', '081234567892', 'SPP003'),
('1234567893', '12345681', 'Dian Permata', 'KLS004', 'XI TKJ 2', 'Jl. Diponegoro No. 40 Semarang', '081234567893', 'SPP004'),
('1234567894', '12345682', 'Eko Saputra', 'KLS005', 'XII MM 1', 'Jl. Ahmad Yani No. 50 Yogyakarta', '081234567894', 'SPP005');

-- Insert Data Pembayaran (2 rows - transactions)
INSERT INTO tb_pembayaran VALUES 
('BYR001', 'Sudah Lunas', '1234567890', '2024-01-15', '2024-01-15', '2024-01-20', '1', 'SPP001', '500000', '500000', '0'),
('BYR002', 'Sudah Lunas', '1234567891', '2024-01-16', '2024-01-16', '2024-01-21', '1', 'SPP002', '550000', '600000', '50000');

-- Insert Data Cek Pembayaran (2 rows - transactions)
INSERT INTO cek_pembayaran VALUES 
('1234567890', '2024-01-15', '2024-01-20', 'Sudah Lunas', '1', 'Andi Pratama', '081234567890'),
('1234567891', '2024-01-16', '2024-01-21', 'Sudah Lunas', '1', 'Budi Setiawan', '081234567891');
