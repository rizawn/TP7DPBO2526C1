-- =====================================================================
-- DATABASE: db_pustaka_tp7
-- Tugas Praktikum 7 - Desain dan Pemrograman Berorientasi Objek (DPBO)
-- Nama : Riza Wahyu Nugraha
-- NIM  : 2511421
-- =====================================================================

CREATE DATABASE IF NOT EXISTS db_pustaka_tp7 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_pustaka_tp7;

-- ---------------------------------------------------------------------
-- 1. TABEL: categories (Entitas Kategori Buku)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS loans;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS members;
DROP TABLE IF EXISTS categories;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    shelf_location VARCHAR(50) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 2. TABEL: books (Entitas Buku - Relasi FK ke categories)
-- ---------------------------------------------------------------------
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(150) NOT NULL,
    publisher VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    category_id INT NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    cover_url VARCHAR(255) NULL,
    synopsis TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_books_category FOREIGN KEY (category_id) 
        REFERENCES categories(id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 3. TABEL: members (Entitas Anggota Perpustakaan)
-- ---------------------------------------------------------------------
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_code VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(25) NOT NULL,
    address TEXT NOT NULL,
    status ENUM('Aktif', 'Non-Aktif') DEFAULT 'Aktif',
    join_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 4. TABEL: loans (Entitas Sirkulasi Peminjaman - FK ke books & members)
-- ---------------------------------------------------------------------
CREATE TABLE loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_code VARCHAR(35) NOT NULL UNIQUE,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('Dipinjam', 'Dikembalikan', 'Terlambat') DEFAULT 'Dipinjam',
    fine DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_loans_book FOREIGN KEY (book_id) 
        REFERENCES books(id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
    CONSTRAINT fk_loans_member FOREIGN KEY (member_id) 
        REFERENCES members(id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- DATA SEEDING (Data Awal untuk Pengujian Fungsionalitas)
-- =====================================================================

-- Seed Kategori
INSERT INTO categories (code, name, shelf_location, description) VALUES
('CAT-CS', 'Teknologi & Ilmu Komputer', 'Rak A-01', 'Buku-buku seputar pemrograman, rekayasa perangkat lunak, AI, dan sistem informasi.'),
('CAT-SC', 'Sains & Matematika', 'Rak B-02', 'Buku teori sains, kalkulus, statistika, fisika, dan logika komputasi.'),
('CAT-LIT', 'Sastra & Fiksi', 'Rak C-03', 'Novel fiksi, karya sastra klasik, antologi puisi, dan karya fiksi ilmiah populer.'),
('CAT-SLF', 'Pengembangan Diri & Karir', 'Rak D-04', 'Buku psikologi terapan, manajemen waktu, pembentukan kebiasaan, dan leadership.');

-- Seed Buku
INSERT INTO books (isbn, title, author, publisher, year, category_id, stock, cover_url, synopsis) VALUES
('9780132350884', 'Clean Code: A Handbook of Agile Software Craftsmanship', 'Robert C. Martin', 'Prentice Hall', 2008, 1, 5, 'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?w=400', 'Panduan utama untuk menulis kode yang bersih, mudah dipelihara, dan modular bagi software engineer profesional.'),
('9780201485677', 'Refactoring: Improving the Design of Existing Code', 'Martin Fowler', 'Addison-Wesley', 1999, 1, 4, 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400', 'Prinsip-prinsip merombak struktur kode internal tanpa mengubah perilaku eksternal perangkat lunak.'),
('9780735211292', 'Atomic Habits: Perubahan Kecil yang Memberikan Hasil Luar Biasa', 'James Clear', 'Gramedia Pustaka Utama', 2018, 4, 6, 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=400', 'Metode terbukti untuk membentuk kebiasaan baik dan menghilangkan kebiasaan buruk dengan strategi perubahan 1% setiap hari.'),
('9786020633071', 'Sebuah Seni untuk Bersikap Bodo Amat', 'Mark Manson', 'Grasindo', 2016, 4, 3, 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400', 'Pendekatan yang jujur dan menyegarkan dalam menyikapi berbagai tantangan hidup tanpa memusingkan hal tidak penting.'),
('9780134685991', 'Effective Java (3rd Edition)', 'Joshua Bloch', 'Addison-Wesley', 2018, 1, 4, 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=400', 'Pedoman praktik terbaik untuk arsitektur berorientasi objek dalam bahasa pemrograman Java.'),
('9780439708180', 'Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', 'Scholastic', 1997, 3, 7, 'https://images.unsplash.com/photo-1621351183012-e2f9972dd9bf?w=400', 'Kisah petualangan Harry Potter pada tahun pertamanya di Sekolah Sihir Hogwarts yang legendaris.');

-- Seed Anggota
INSERT INTO members (member_code, name, email, phone, address, status, join_date) VALUES
('MBR-2026-001', 'Riza Wahyu Nugraha', 'riza.wahyu@upi.edu', '081234567890', 'Jl. Dr. Setiabudi No. 229, Bandung', 'Aktif', '2026-01-15'),
('MBR-2026-002', 'Ahmad Farhan Wijaya', 'farhan.wijaya@gmail.com', '082198765432', 'Jl. Gegerkalong Hilir No. 45, Bandung', 'Aktif', '2026-02-10'),
('MBR-2026-003', 'Siti Nur Aisyah', 'siti.aisyah@student.upi.edu', '085712345678', 'Jl. Sariasih No. 12, Bandung', 'Aktif', '2026-03-01'),
('MBR-2026-004', 'Budi Pratama Santoso', 'budi.santoso@outlook.com', '087811223344', 'Jl. Sukajadi No. 88, Bandung', 'Non-Aktif', '2025-11-20');

-- Seed Peminjaman
INSERT INTO loans (loan_code, book_id, member_id, loan_date, due_date, return_date, status, fine, notes) VALUES
('PINJAM-202611-001', 1, 1, '2026-11-01', '2026-11-08', '2026-11-07', 'Dikembalikan', 0.00, 'Pengembalian tepat waktu, buku dalam kondisi prima.'),
('PINJAM-202611-002', 3, 2, '2026-11-03', '2026-11-10', NULL, 'Dipinjam', 0.00, 'Peminjaman untuk bahan tugas mata kuliah.'),
('PINJAM-202611-003', 2, 3, '2026-10-25', '2026-11-01', NULL, 'Terlambat', 9000.00, 'Terlambat 9 hari dari batas jatuh tempo (Rp 1.000 / hari).'),
('PINJAM-202611-004', 5, 1, '2026-11-05', '2026-11-12', NULL, 'Dipinjam', 0.00, 'Peminjaman referensi praktikum DPBO Java.');

