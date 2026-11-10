<?php
/**
 * Class Book
 * Mengelola operasi CRUD tabel 'books'
 * Berelasi dengan tabel 'categories' (Foreign Key: category_id)
 * Menggunakan PDO Prepared Statement secara penuh
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 */

require_once __DIR__ . '/../config/db.php';

class Book {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->conn;
    }

    /**
     * Mengambil seluruh data buku beserta relasi kategori (READ)
     * Dilengkapi parameter pencarian dan filter kategori
     */
    public function getAll($keyword = null, $categoryId = null) {
        $sql = "SELECT b.*, c.name AS category_name, c.code AS category_code, c.shelf_location 
                FROM books b 
                JOIN categories c ON b.category_id = c.id 
                WHERE 1=1";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (b.title LIKE :kw OR b.author LIKE :kw OR b.isbn LIKE :kw OR b.publisher LIKE :kw)";
            $params[':kw'] = '%' . trim($keyword) . '%';
        }

        if (!empty($categoryId)) {
            $sql .= " AND b.category_id = :catId";
            $params[':catId'] = (int)$categoryId;
        }

        $sql .= " ORDER BY b.id DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $val, $type);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mengambil 1 data buku lengkap berdasarkan ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT b.*, c.name AS category_name, c.code AS category_code, c.shelf_location 
             FROM books b 
             JOIN categories c ON b.category_id = c.id 
             WHERE b.id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Memeriksa apakah ISBN sudah terdaftar
     */
    public function isIsbnExists($isbn, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM books WHERE isbn = :isbn";
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':isbn', trim($isbn), PDO::PARAM_STR);
        if ($excludeId !== null) {
            $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Menambahkan data buku baru (CREATE)
     */
    public function create($isbn, $title, $author, $publisher, $year, $category_id, $stock, $cover_url, $synopsis) {
        if ($this->isIsbnExists($isbn)) {
            return ['success' => false, 'message' => "ISBN '{$isbn}' sudah terdaftar dalam sistem!"];
        }

        if ((int)$stock < 0) {
            return ['success' => false, 'message' => 'Jumlah stok buku tidak boleh bernilai negatif!'];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO books (isbn, title, author, publisher, year, category_id, stock, cover_url, synopsis) 
             VALUES (:isbn, :title, :author, :publisher, :year, :cat_id, :stock, :cover, :synopsis)"
        );
        $stmt->bindValue(':isbn', trim($isbn), PDO::PARAM_STR);
        $stmt->bindValue(':title', trim($title), PDO::PARAM_STR);
        $stmt->bindValue(':author', trim($author), PDO::PARAM_STR);
        $stmt->bindValue(':publisher', trim($publisher), PDO::PARAM_STR);
        $stmt->bindValue(':year', (int)$year, PDO::PARAM_INT);
        $stmt->bindValue(':cat_id', (int)$category_id, PDO::PARAM_INT);
        $stmt->bindValue(':stock', (int)$stock, PDO::PARAM_INT);
        $stmt->bindValue(':cover', trim($cover_url), PDO::PARAM_STR);
        $stmt->bindValue(':synopsis', trim($synopsis), PDO::PARAM_STR);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Buku baru berhasil ditambahkan!'];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan buku.'];
    }

    /**
     * Memperbarui informasi data buku (UPDATE)
     */
    public function update($id, $isbn, $title, $author, $publisher, $year, $category_id, $stock, $cover_url, $synopsis) {
        if ($this->isIsbnExists($isbn, $id)) {
            return ['success' => false, 'message' => "ISBN '{$isbn}' sudah digunakan oleh buku lain!"];
        }

        if ((int)$stock < 0) {
            return ['success' => false, 'message' => 'Jumlah stok buku tidak boleh bernilai negatif!'];
        }

        $stmt = $this->db->prepare(
            "UPDATE books 
             SET isbn = :isbn, title = :title, author = :author, publisher = :publisher, 
                 year = :year, category_id = :cat_id, stock = :stock, cover_url = :cover, synopsis = :synopsis 
             WHERE id = :id"
        );
        $stmt->bindValue(':isbn', trim($isbn), PDO::PARAM_STR);
        $stmt->bindValue(':title', trim($title), PDO::PARAM_STR);
        $stmt->bindValue(':author', trim($author), PDO::PARAM_STR);
        $stmt->bindValue(':publisher', trim($publisher), PDO::PARAM_STR);
        $stmt->bindValue(':year', (int)$year, PDO::PARAM_INT);
        $stmt->bindValue(':cat_id', (int)$category_id, PDO::PARAM_INT);
        $stmt->bindValue(':stock', (int)$stock, PDO::PARAM_INT);
        $stmt->bindValue(':cover', trim($cover_url), PDO::PARAM_STR);
        $stmt->bindValue(':synopsis', trim($synopsis), PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Data buku berhasil diperbarui!'];
        }
        return ['success' => false, 'message' => 'Gagal memperbarui data buku.'];
    }

    /**
     * Menghapus buku (DELETE)
     * Menguji foreign key integrity terhadap transaksi peminjaman
     */
    public function delete($id) {
        $activeLoans = $this->db->prepare(
            "SELECT COUNT(*) FROM loans WHERE book_id = :id AND status = 'Dipinjam'"
        );
        $activeLoans->bindValue(':id', $id, PDO::PARAM_INT);
        $activeLoans->execute();
        if ($activeLoans->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Buku tidak dapat dihapus karena saat ini masih berada dalam masa pinjam aktif oleh anggota!'
            ];
        }

        // Cek riwayat peminjaman (sudah dikembalikan)
        $historyLoans = $this->db->prepare("SELECT COUNT(*) FROM loans WHERE book_id = :id");
        $historyLoans->bindValue(':id', $id, PDO::PARAM_INT);
        $historyLoans->execute();
        if ($historyLoans->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Buku memiliki catatan riwayat peminjaman arsip. Hapus riwayat terkait terlebih dahulu untuk menjaga konsistensi database.'
            ];
        }

        $stmt = $this->db->prepare("DELETE FROM books WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Buku berhasil dihapus dari sistem!'];
        }
        return ['success' => false, 'message' => 'Gagal menghapus buku.'];
    }

    /**
     * Mengubah kuantitas stok buku (Increment / Decrement)
     * Digunakan secara internal saat peminjaman dan pengembalian
     */
    public function updateStock($id, $delta) {
        $stmt = $this->db->prepare(
            "UPDATE books SET stock = stock + :delta WHERE id = :id AND (stock + :delta) >= 0"
        );
        $stmt->bindValue(':delta', (int)$delta, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Menghitung total judul buku
     */
    public function count() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM books");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Menghitung total seluruh eksemplar stok buku fisik
     */
    public function sumStock() {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(stock), 0) FROM books");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Mendapatkan daftar buku dengan stok menipis (<= threshold)
     */
    public function getLowStock($threshold = 3) {
        $stmt = $this->db->prepare(
            "SELECT b.*, c.name AS category_name 
             FROM books b 
             JOIN categories c ON b.category_id = c.id 
             WHERE b.stock <= :threshold 
             ORDER BY b.stock ASC"
        );
        $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>

