CREATE DATABASE IF NOT EXISTS db_reservasi_ruangan;
USE db_reservasi_ruangan;

DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','dosen','mahasiswa') NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    nim_nidn VARCHAR(50) DEFAULT NULL,
    department VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150) NOT NULL,
    capacity INT NOT NULL,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    purpose TEXT NOT NULL,
    reservation_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    participants INT NOT NULL,
    document VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','verified','approved','rejected','cancelled') DEFAULT 'pending',
    admin_note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservation_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reservation_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password, role, phone, nim_nidn, department) VALUES
('Administrator', 'admin@example.com', '$2y$12$H1OkoVAw4jxpgwPZD3GLAuCZOUnZQdbw1yc/QGaME/ylUD5Rj0KVG', 'admin', '081234567890', 'ADM001', 'Umum'),
('Dosen Demo', 'dosen@example.com', '$2y$12$CkzsTKJUPlGi5XUrEnuHneeX9IYbxRLZUUOBcvg21b8.5WWcY31v.', 'dosen', '081111111111', 'NIDN001', 'Teknik Informatika'),
('Mahasiswa Demo', 'mahasiswa@example.com', '$2y$12$4gXzZ7l900dxdHKjRM8UtuajsJp7imG/vLWz2Ly52yiui2OScqN3e', 'mahasiswa', '082222222222', '22123456', 'Sistem Informasi');

INSERT INTO rooms (name, location, capacity, status, description) VALUES
('Ruang Seminar 1', 'Gedung A Lantai 2', 50, 'aktif', 'Ruangan untuk seminar dan presentasi.'),
('Lab Komputer 2', 'Gedung B Lantai 1', 35, 'aktif', 'Laboratorium komputer untuk praktikum.'),
('Ruang Rapat Pimpinan', 'Gedung C Lantai 3', 20, 'aktif', 'Ruangan rapat internal.'),
('Aula Kecil', 'Gedung D Lantai Dasar', 80, 'aktif', 'Aula untuk kegiatan kampus skala sedang.');

INSERT INTO reservations (user_id, room_id, title, purpose, reservation_date, start_time, end_time, participants, status, admin_note) VALUES
(2, 1, 'Seminar Metodologi Penelitian', 'Kegiatan seminar dosen bersama mahasiswa.', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '09:30:00', 40, 'approved', 'Disetujui admin.'),
(3, 2, 'Diskusi Kelompok', 'Diskusi tugas akhir mahasiswa.', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', '13:30:00', 10, 'pending', 'Menunggu verifikasi admin.');
