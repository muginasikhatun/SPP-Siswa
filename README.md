# Aplikasi Web SPP Siswa

Sistem informasi manajemen pembayaran SPP (Sumbangan Pembinaan Pendidikan) siswa berbasis web menggunakan PHP dan MySQL.

## Deskripsi

Aplikasi ini digunakan untuk mengelola pembayaran SPP siswa, termasuk manajemen data master (kelas, SPP, petugas, siswa) dan transaksi pembayaran.

## Fitur

- **Login System**: Autentikasi pengguna dengan level akses (admin, petugas, siswa)
- **Dashboard**: Ringkasan statistik data
- **Manajemen Kelas**: CRUD data kelas dan kompetensi keahlian
- **Manajemen SPP**: CRUD data SPP dengan tahun dan nominal
- **Manajemen Petugas**: CRUD data petugas dengan level akses
- **Manajemen Siswa**: CRUD data siswa lengkap
- **Pembayaran**: Input dan kelola transaksi pembayaran SPP
- **Cek Pembayaran**: Monitoring status pembayaran siswa dengan pencarian berdasarkan NISN atau Nama

## Struktur Database

### tb_kelas
- `id_kelas` (VARCHAR 11) - Primary Key
- `nama_kelas` (VARCHAR 10)
- `komp_keahlian` (VARCHAR 50)

### tb_spp
- `id_spp` (VARCHAR 11) - Primary Key
- `tahun` (INT 11)
- `nominal` (VARCHAR 40)

### tb_petugas
- `id_petugas` (VARCHAR 11) - Primary Key
- `username` (VARCHAR 25)
- `password` (VARCHAR 32)
- `nama_petugas` (VARCHAR 35)
- `level` (ENUM: admin, petugas, siswa)

### tb_siswa
- `nisn` (VARCHAR 10) - Primary Key
- `nis` (VARCHAR 8)
- `nama` (VARCHAR 50)
- `id_kelas` (VARCHAR 11) - Foreign Key ke tb_kelas
- `nama_kelas` (VARCHAR 10)
- `alamat` (TEXT)
- `no_telp` (VARCHAR 13)
- `id_spp` (VARCHAR 40)

### tb_pembayaran
- `id_pembayaran` (VARCHAR 11) - Primary Key
- `status` (ENUM: belum lunas, sudah lunas)
- `nisn` (VARCHAR 10) - Foreign Key ke tb_siswa
- `tgl_bayar` (DATE)
- `tgl_terakhir_bayar` (DATE)
- `batas_pembayaran` (DATE)
- `jumlah_bulan` (VARCHAR 10)
- `id_spp` (VARCHAR 40)
- `nominal_bayar` (VARCHAR 100)
- `jumlah_bayar` (VARCHAR 40)
- `kembalian` (VARCHAR 100)

### tb_cek_pembayaran
- `nisn` (VARCHAR 10) - Primary Key
- `tgl_terakhir_bayar` (DATE)
- `tgl_sekarang` (DATE)
- `status_pembayaran` (ENUM: belum lunas, sudah lunas)
- `jumlah_bulan` (VARCHAR 5)
- `nama` (VARCHAR 50)
- `no_telp` (VARCHAR 13)

## Persyaratan Sistem

- XAMPP (Apache + MySQL)
- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB
- Web browser modern (Chrome, Firefox, Edge)

## Instalasi

1. **Copy folder project** ke `C:\laragon\www\SPP-Siswa` (jika menggunakan Laragon) atau `C:\xampp\htdocs\SPP-Siswa` (jika menggunakan XAMPP)

2. **Buka phpMyAdmin** dan buat database baru:
   - Nama database: `spp_siswa`

3. **Import database**:
   - Buka file `database/spp_siswa.sql` di phpMyAdmin
   - Atau jalankan perintah SQL secara manual

4. **Import data uji coba** (opsional):
   - Buka file `database/test_data.sql` di phpMyAdmin
   - Ini akan menambahkan 5 baris data master dan 2 baris transaksi

5. **Konfigurasi koneksi database** (jika diperlukan):
   - Edit file `config/koneksi.php`
   - Sesuaikan host, user, password, dan nama database

6. **Akses aplikasi**:
   - Buka browser dan kunjungi: `http://localhost/SPP-Siswa`

## Login Default

- **Username**: admin
- **Password**: admin123

## Data Uji Coba

Aplikasi sudah dilengkapi dengan data uji coba:

### Master Data (5 baris per tabel)
- **Kelas**: 5 kelas (X RPL 1, X RPL 2, XI TKJ 1, XI TKJ 2, XII MM 1)
- **SPP**: 5 data SPP dengan nominal berbeda
- **Petugas**: 5 petugas dengan berbagai level akses
- **Siswa**: 5 siswa dengan data lengkap

### Transaksi (2 baris per tabel)
- **Pembayaran**: 2 transaksi pembayaran
- **Cek Pembayaran**: 2 data cek pembayaran

## Struktur Folder

```
SPP-Siswa/
├── config/
│   └── koneksi.php          # File koneksi database
├── database/
│   ├── spp_siswa.sql        # Struktur database
│   └── test_data.sql        # Data uji coba
├── cek_pembayaran.php       # Halaman cek pembayaran
├── dashboard.php            # Halaman dashboard
├── index.php                # Halaman login
├── kelas.php                # CRUD data kelas
├── login_process.php        # Proses login
├── logout.php               # Proses logout
├── pembayaran.php           # CRUD pembayaran
├── petugas.php              # CRUD petugas
├── siswa.php                # CRUD siswa
├── spp.php                  # CRUD SPP
└── README.md                # Dokumentasi
```

## Penggunaan

1. Login dengan akun admin atau petugas
2. Navigasi menggunakan menu di sidebar
3. Tambah, edit, atau hapus data sesuai kebutuhan
4. Input transaksi pembayaran SPP
5. Cek status pembayaran siswa dengan memasukkan NISN atau Nama siswa

## Teknologi yang Digunakan

- **Backend**: PHP Native
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Icons**: Font Awesome 6

## Catatan

- Password disimpan dalam bentuk plain text (untuk keperluan pembelajaran)
- Aplikasi ini menggunakan session untuk autentikasi
- Desain responsif dengan Bootstrap 5
- Gradient sidebar untuk tampilan modern

## Troubleshooting

**Masalah: Tidak bisa login**
- Pastikan database sudah diimport dengan benar
- Cek file `config/koneksi.php` untuk konfigurasi database

**Masalah: Error koneksi database**
- Pastikan MySQL/MariaDB sudah berjalan
- Cek username dan password database di `config/koneksi.php`

**Masalah: Halaman blank**
- Pastikan ekstensi PHP sudah aktif
- Cek error log di XAMPP/Laragon

## Lisensi

Proyek ini dibuat untuk keperluan pembelajaran dan tugas sekolah.

## Kontak

Untuk pertanyaan atau masalah, silakan hubungi pengembang.
