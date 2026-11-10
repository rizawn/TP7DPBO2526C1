<?php
/**
 * Class Loan
 * Mengelola operasi CRUD sirkulasi peminjaman dan pengembalian buku (tabel 'loans')
 * Berelasi ganda (Foreign Keys):
 * - book_id   -> books(id)
 * - member_id -> members(id)
 * Menggunakan PDO Prepared Statement & Database Transaction secara penuh
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/Book.php';
require_once __DIR__ . '/Member.php';

class Loan {
    private $db;
    const TARIF_DENDA_PER_HARI = 1000; // Rp 1.000 / hari keterlambatan

    public function __construct() {
        $database = new Database();
        $this->db = $database->conn;
    }

    /**
     * Mengambil seluruh data sirkulasi peminjaman (READ)
     * Menggabungkan data relasi Buku, Kategori, dan Anggota via JOIN
     */
    public function getAll($keyword = null, $status = null) {
        $sql = "SELECT l.*, 
                       b.title AS book_title, b.isbn AS book_isbn, b.cover_url AS book_cover,
                       c.name AS category_name,
                       m.name AS member_name, m.member_code, m.phone AS member_phone,
                       DATEDIFF(CURDATE(), l.due_date) AS days_late
                FROM loans l 
                JOIN books b ON l.book_id = b.id 
                JOIN categories c ON b.category_id = c.id 
                JOIN members m ON l.member_id = m.id 
                WHERE 1=1";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (l.loan_code LIKE :kw OR b.title LIKE :kw OR m.name LIKE :kw OR m.member_code LIKE :kw)";
            $params[':kw'] = '%' . trim($keyword) . '%';
        }

        if (!empty($status)) {
            if ($status === 'Terlambat') {
                $sql .= " AND (l.status = 'Terlambat' OR (l.status = 'Dipinjam' AND CURDATE() > l.due_date))";
            } else {
                $sql .= " AND l.status = :status";
                $params[':status'] = trim($status);
            }
        }

        $sql .= " ORDER BY l.id DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        }
        $stmt->execute();
        $loans = $stmt->fetchAll();

        // Hitung denda berjalan secara otomatis jika sedang dipinjam dan melewati jatuh tempo
        foreach ($loans as &$row) {
            if ($row['status'] === 'Dipinjam' && (int)$row['days_late'] > 0) {
                $row['dynamic_status'] = 'Terlambat';
                $row['calculated_fine'] = (int)$row['days_late'] * self::TARIF_DENDA_PER_HARI;
            } else {
                $row['dynamic_status'] = $row['status'];
                $row['calculated_fine'] = (float)$row['fine'];
            }
        }
        return $loans;
    }

    /**
     * Mengambil 1 data peminjaman berdasarkan ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT l.*, 
                    b.title AS book_title, b.isbn AS book_isbn, b.stock AS current_book_stock,
                    m.name AS member_name, m.member_code, m.email AS member_email,
                    DATEDIFF(CURDATE(), l.due_date) AS days_late
             FROM loans l 
             JOIN books b ON l.book_id = b.id 
             JOIN members m ON l.member_id = m.id 
             WHERE l.id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row) {
            if ($row['status'] === 'Dipinjam' && (int)$row['days_late'] > 0) {
                $row['dynamic_status'] = 'Terlambat';
                $row['calculated_fine'] = (int)$row['days_late'] * self::TARIF_DENDA_PER_HARI;
            } else {
                $row['dynamic_status'] = $row['status'];
                $row['calculated_fine'] = (float)$row['fine'];
            }
        }
        return $row;
    }

    /**
     * Membuat transaksi peminjaman baru (CREATE)
     * Mengimplementasikan Transaction & Atomic Stock Decrement
     */
    public function borrow($book_id, $member_id, $loan_date, $due_date, $notes = '') {
        // Validasi input tanggal
        if (strtotime($due_date) < strtotime($loan_date)) {
            return ['success' => false, 'message' => 'Batas tanggal kembali tidak boleh lebih awal dari tanggal pinjam!'];
        }

        // Cek status keaktifan anggota
        $stmtMbr = $this->db->prepare("SELECT status, name FROM members WHERE id = :id");
        $stmtMbr->bindValue(':id', (int)$member_id, PDO::PARAM_INT);
        $stmtMbr->execute();
        $member = $stmtMbr->fetch();

        if (!$member) {
            return ['success' => false, 'message' => 'Data anggota tidak ditemukan!'];
        }
        if ($member['status'] !== 'Aktif') {
            return ['success' => false, 'message' => "Anggota '{$member['name']}' berstatus Non-Aktif dan tidak diizinkan meminjam buku!"];
        }

        // Cek ketersediaan stok buku
        $stmtBook = $this->db->prepare("SELECT title, stock FROM books WHERE id = :id FOR UPDATE");
        
        try {
            $this->db->beginTransaction();

            $stmtBook->bindValue(':id', (int)$book_id, PDO::PARAM_INT);
            $stmtBook->execute();
            $book = $stmtBook->fetch();

            if (!$book) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Data buku tidak ditemukan!'];
            }
            if ((int)$book['stock'] <= 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => "Stok buku '{$book['title']}' sedang kosong (0)!"];
            }

            // Generate Kode Transaksi Unik
            $loanCode = 'PINJAM-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -4));

            // Simpan data peminjaman
            $stmtInsert = $this->db->prepare(
                "INSERT INTO loans (loan_code, book_id, member_id, loan_date, due_date, status, fine, notes) 
                 VALUES (:code, :book_id, :member_id, :loan_date, :due_date, 'Dipinjam', 0.00, :notes)"
            );
            $stmtInsert->bindValue(':code', $loanCode, PDO::PARAM_STR);
            $stmtInsert->bindValue(':book_id', (int)$book_id, PDO::PARAM_INT);
            $stmtInsert->bindValue(':member_id', (int)$member_id, PDO::PARAM_INT);
            $stmtInsert->bindValue(':loan_date', $loan_date, PDO::PARAM_STR);
            $stmtInsert->bindValue(':due_date', $due_date, PDO::PARAM_STR);
            $stmtInsert->bindValue(':notes', trim($notes), PDO::PARAM_STR);
            $stmtInsert->execute();

            // Kurangi stok buku secara otomatis (-1)
            $stmtDecr = $this->db->prepare("UPDATE books SET stock = stock - 1 WHERE id = :id");
            $stmtDecr->bindValue(':id', (int)$book_id, PDO::PARAM_INT);
            $stmtDecr->execute();

            $this->db->commit();
            return [
                'success' => true,
                'message' => "Peminjaman buku '{$book['title']}' berhasil dicatat dengan kode transaksi {$loanCode}!"
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Gagal memproses transaksi peminjaman: ' . $e->getMessage()];
        }
    }

    /**
     * Memproses pengembalian buku (UPDATE Status -> 'Dikembalikan')
     * Mengkalkulasi denda keterlambatan dan menambah stok buku (+1)
     */
    public function returnBook($id, $return_date = null, $notes = '') {
        $loan = $this->getById($id);
        if (!$loan) {
            return ['success' => false, 'message' => 'Data peminjaman tidak ditemukan!'];
        }
        if ($loan['status'] === 'Dikembalikan') {
            return ['success' => false, 'message' => 'Buku ini sudah tercatat telah dikembalikan sebelumnya!'];
        }

        $retDate = !empty($return_date) ? $return_date : date('Y-m-d');
        
        // Kalkulasi keterlambatan
        $dueDateTs = strtotime($loan['due_date']);
        $retDateTs = strtotime($retDate);
        $fine = 0.00;

        if ($retDateTs > $dueDateTs) {
            $diffDays = (int)ceil(($retDateTs - $dueDateTs) / 86400);
            $fine = $diffDays * self::TARIF_DENDA_PER_HARI;
        }

        try {
            $this->db->beginTransaction();

            $updatedNotes = trim($notes);
            if (!empty($loan['notes']) && !empty($updatedNotes)) {
                $finalNotes = $loan['notes'] . " | Pengembalian: " . $updatedNotes;
            } elseif (!empty($updatedNotes)) {
                $finalNotes = $updatedNotes;
            } else {
                $finalNotes = $loan['notes'];
            }

            $stmtUpdate = $this->db->prepare(
                "UPDATE loans 
                 SET return_date = :ret_date, status = 'Dikembalikan', fine = :fine, notes = :notes 
                 WHERE id = :id"
            );
            $stmtUpdate->bindValue(':ret_date', $retDate, PDO::PARAM_STR);
            $stmtUpdate->bindValue(':fine', $fine, PDO::PARAM_STR);
            $stmtUpdate->bindValue(':notes', $finalNotes, PDO::PARAM_STR);
            $stmtUpdate->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $stmtUpdate->execute();

            // Tambah stok buku kembali (+1)
            $stmtIncr = $this->db->prepare("UPDATE books SET stock = stock + 1 WHERE id = :id");
            $stmtIncr->bindValue(':id', (int)$loan['book_id'], PDO::PARAM_INT);
            $stmtIncr->execute();

            $this->db->commit();
            $msg = "Buku '{$loan['book_title']}' berhasil dikembalikan.";
            if ($fine > 0) {
                $msg .= " Terlambat dengan total denda: Rp " . number_format($fine, 0, ',', '.');
            }
            return ['success' => true, 'message' => $msg];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Gagal memproses pengembalian: ' . $e->getMessage()];
        }
    }

    /**
     * Memperbarui detail transaksi peminjaman (UPDATE)
     */
    public function update($id, $due_date, $notes) {
        $stmt = $this->db->prepare(
            "UPDATE loans SET due_date = :due_date, notes = :notes WHERE id = :id"
        );
        $stmt->bindValue(':due_date', trim($due_date), PDO::PARAM_STR);
        $stmt->bindValue(':notes', trim($notes), PDO::PARAM_STR);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Data peminjaman berhasil diperbarui!'];
        }
        return ['success' => false, 'message' => 'Gagal memperbarui data peminjaman.'];
    }

    /**
     * Menghapus catatan peminjaman (DELETE)
     * Mengembalikan stok buku jika status peminjaman masih aktif saat dihapus
     */
    public function delete($id) {
        $loan = $this->getById($id);
        if (!$loan) {
            return ['success' => false, 'message' => 'Data peminjaman tidak ditemukan!'];
        }

        try {
            $this->db->beginTransaction();

            // Jika buku belum dikembalikan dan catatannya dihapus paksa, pulihkan stok fisik
            if ($loan['status'] !== 'Dikembalikan') {
                $stmtIncr = $this->db->prepare("UPDATE books SET stock = stock + 1 WHERE id = :book_id");
                $stmtIncr->bindValue(':book_id', (int)$loan['book_id'], PDO::PARAM_INT);
                $stmtIncr->execute();
            }

            $stmtDel = $this->db->prepare("DELETE FROM loans WHERE id = :id");
            $stmtDel->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $stmtDel->execute();

            $this->db->commit();
            return ['success' => true, 'message' => 'Catatan peminjaman berhasil dihapus!'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Gagal menghapus data peminjaman: ' . $e->getMessage()];
        }
    }

    /**
     * Menghitung total transaksi peminjaman
     */
    public function count() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM loans");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Menghitung jumlah peminjaman yang sedang aktif (belum dikembalikan)
     */
    public function countActive() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM loans WHERE status = 'Dipinjam'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Menghitung jumlah peminjaman yang terlambat
     */
    public function countOverdue() {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM loans 
             WHERE status = 'Terlambat' OR (status = 'Dipinjam' AND CURDATE() > due_date)"
        );
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Menghitung total penerimaan kas denda keterlambatan
     */
    public function sumFine() {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(fine), 0) FROM loans WHERE status = 'Dikembalikan'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
?>

