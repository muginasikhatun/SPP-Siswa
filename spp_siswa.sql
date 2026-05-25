-- Database: spp_siswa
CREATE DATABASE IF NOT EXISTS db_spp;
USE db_spp;

-- Table: tb_spp (Master Table)
CREATE TABLE tb_spp (
    id_spp VARCHAR(11) PRIMARY KEY,
    tahun INT(11),
    nominal VARCHAR(40)
);

-- Table: tb_petugas (Master Table)
CREATE TABLE tb_petugas (
    id_petugas VARCHAR(11) PRIMARY KEY,
    username VARCHAR(25),
    password VARCHAR(32),
    nama_petugas VARCHAR(35),
    level ENUM('admin', 'petugas', 'siswa')
);

-- Table: tb_kelas (Master Table)
CREATE TABLE tb_kelas (
    id_kelas VARCHAR(11) PRIMARY KEY,
    nama_kelas VARCHAR(10) UNIQUE,
    komp_keahlian VARCHAR(50)
);

-- Table: tb_siswa (Master Table)
CREATE TABLE tb_siswa (
    nisn VARCHAR(10) PRIMARY KEY,
    nis VARCHAR(8),
    nama VARCHAR(50) UNIQUE,
    id_kelas VARCHAR(11),
    nama_kelas VARCHAR(10),
    alamat TEXT,
    no_telp VARCHAR(13) UNIQUE,
    id_spp VARCHAR(40) UNIQUE,
    FOREIGN KEY (id_kelas) REFERENCES tb_kelas(id_kelas),
    FOREIGN KEY (nama_kelas) REFERENCES tb_kelas(nama_kelas)
);

-- Table: tb_pembayaran (Transaction Table)
CREATE TABLE tb_pembayaran (
    id_pembayaran VARCHAR(11) PRIMARY KEY,
    status ENUM('Belum Lunas', 'Sudah Lunas'),
    nisn VARCHAR(10),
    tgl_bayar DATE,
    tgl_terakhir_bayar DATE,
    batas_pembayaran DATE,
    jumlah_bulan VARCHAR(10),
    id_spp VARCHAR(40),
    nominal_bayar VARCHAR(100),
    jumlah_bayar VARCHAR(40),
    kembalian VARCHAR(100),
    FOREIGN KEY (nisn) REFERENCES tb_siswa(nisn),
    FOREIGN KEY (id_spp) REFERENCES tb_siswa(id_spp)
);

-- Table: cek_pembayaran (Transaction Table)
CREATE TABLE cek_pembayaran (
    nisn VARCHAR(10),
    tgl_terakhir_bayar DATE,
    tgl_sekarang DATE,
    status_pembayaran ENUM('Belum Lunas', 'Sudah Lunas'),
    jumlah_bulan VARCHAR(5),
    nama VARCHAR(50),
    no_telp VARCHAR(13),
    FOREIGN KEY (nisn) REFERENCES tb_siswa(nisn),
    FOREIGN KEY (nama) REFERENCES tb_siswa(nama),
    FOREIGN KEY (no_telp) REFERENCES tb_siswa(no_telp)
);
