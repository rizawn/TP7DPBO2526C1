<?php
/**
 * Class Category
 * Mengelola operasi CRUD tabel 'categories'
 * Menggunakan PDO Prepared Statement secara penuh
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 */

require_once __DIR__ . '/../config/db.php';

class Category {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->conn;
    }

    /**
     * Mengambil seluruh data kategori dengan kalkulasi jumlah buku terkait
     */
    public function getAll($keyword = null) {
        if (!empty($keyword)) {
            $stmt = $this->db->prepare(
                "SELECT c.*, (SELECT COUNT(*) FROM books WHERE category_id = c.id) AS total_books 
                 FROM categories c 
                 WHERE c.name LIKE :kw OR c.code LIKE :kw OR c.shelf_location LIKE :kw 
                 ORDER BY c.id DESC"
            );
            $param = '%' . trim($keyword) . '%';
            $stmt->bindValue(':kw', $param, PDO::PARAM_STR);
        } else {
            $stmt = $this->db->prepare(
                "SELECT c.*, (SELECT COUNT(*) FROM books WHERE category_id = c.id) AS total_books 
                 FROM categories c 
                 ORDER BY c.id DESC"
            );
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mengambil 1 data kategori berdasarkan ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Memeriksa apakah kode kategori sudah pernah digunakan
     */
    public function isCodeExists($code, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM categories WHERE code = :code";
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':code', trim($code), PDO::PARAM_STR);
        if ($excludeId !== null) {
            $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Menambahkan kategori baru (CREATE)
     */
    public function create($code, $name, $shelf_location, $description) {
        if ($this->isCodeExists($code)) {
            return ['success' => false, 'message' => "Kode kategori '{$code}' sudah terdaftar!"];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO categories (code, name, shelf_location, description) 
             VALUES (:code, :name, :shelf, :desc)"
        );
        $stmt->bindValue(':code', strtoupper(trim($code)), PDO::PARAM_STR);
        $stmt->bindValue(':name', trim($name), PDO::PARAM_STR);
        $stmt->bindValue(':shelf', trim($shelf_location), PDO::PARAM_STR);
        $stmt->bindValue(':desc', trim($description), PDO::PARAM_STR);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Kategori berhasil ditambahkan!'];
        }
        return ['success' => false, 'message' => 'Gagal menambahkan kategori.'];
    }

    /**
     * Memperbarui data kategori (UPDATE)
     */
    public function update($id, $code, $name, $shelf_location, $description) {
        if ($this->isCodeExists($code, $id)) {
            return ['success' => false, 'message' => "Kode kategori '{$code}' sudah digunakan oleh kategori lain!"];
        }

        $stmt = $this->db->prepare(
            "UPDATE categories 
             SET code = :code, name = :name, shelf_location = :shelf, description = :desc 
             WHERE id = :id"
        );
        $stmt->bindValue(':code', strtoupper(trim($code)), PDO::PARAM_STR);
        $stmt->bindValue(':name', trim($name), PDO::PARAM_STR);
        $stmt->bindValue(':shelf', trim($shelf_location), PDO::PARAM_STR);
        $stmt->bindValue(':desc', trim($description), PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Kategori berhasil diperbarui!'];
        }
        return ['success' => false, 'message' => 'Gagal memperbarui kategori.'];
    }

    /**
     * Menghapus data kategori (DELETE)
     * Menguji foreign key constraint integrity secara terstruktur
     */
    public function delete($id) {
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM books WHERE category_id = :id");
        $checkStmt->bindValue(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();
        $bookCount = $checkStmt->fetchColumn();

        if ($bookCount > 0) {
            return [
                'success' => false,
                'message' => "Kategori tidak dapat dihapus karena masih menaungi {$bookCount} buku!"
            ];
        }

        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Kategori berhasil dihapus!'];
        }
        return ['success' => false, 'message' => 'Gagal menghapus kategori.'];
    }

    /**
     * Menghitung total kategori
     */
    public function count() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
?>

