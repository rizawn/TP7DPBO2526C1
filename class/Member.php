<?php
/**
 * Class Member
 * Mengelola operasi CRUD tabel 'members'
 * Menggunakan PDO Prepared Statement secara penuh
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 */

require_once __DIR__ . '/../config/db.php';

class Member {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->conn;
    }

    /**
     * Mengambil seluruh data anggota (READ)
     * Dilengkapi jumlah pinjaman aktif, pencarian kata kunci, dan filter status
     */
    public function getAll($keyword = null, $status = null) {
        $sql = "SELECT m.*, 
                (SELECT COUNT(*) FROM loans WHERE member_id = m.id AND status = 'Dipinjam') AS active_loans 
                FROM members m 
                WHERE 1=1";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (m.member_code LIKE :kw OR m.name LIKE :kw OR m.email LIKE :kw OR m.phone LIKE :kw)";
            $params[':kw'] = '%' . trim($keyword) . '%';
        }

        if (!empty($status)) {
            $sql .= " AND m.status = :status";
            $params[':status'] = trim($status);
        }

        $sql .= " ORDER BY m.id DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mengambil 1 data anggota berdasarkan ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM members WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Memeriksa keunikan kode anggota
     */
    public function isCodeExists($code, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM members WHERE member_code = :code";
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
     * Memeriksa keunikan email anggota
     */
    public function isEmailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM members WHERE email = :email";
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', trim($email), PDO::PARAM_STR);
        if ($excludeId !== null) {
            $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Menambahkan anggota baru (CREATE)
     */
    public function create($member_code, $name, $email, $phone, $address, $status, $join_date) {
        if ($this->isCodeExists($member_code)) {
            return ['success' => false, 'message' => "Kode anggota '{$member_code}' sudah terdaftar!"];
        }

        if ($this->isEmailExists($email)) {
            return ['success' => false, 'message' => "Email '{$email}' sudah digunakan oleh anggota lain!"];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO members (member_code, name, email, phone, address, status, join_date) 
             VALUES (:code, :name, :email, :phone, :address, :status, :join_date)"
        );
        $stmt->bindValue(':code', strtoupper(trim($member_code)), PDO::PARAM_STR);
        $stmt->bindValue(':name', trim($name), PDO::PARAM_STR);
        $stmt->bindValue(':email', strtolower(trim($email)), PDO::PARAM_STR);
        $stmt->bindValue(':phone', trim($phone), PDO::PARAM_STR);
        $stmt->bindValue(':address', trim($address), PDO::PARAM_STR);
        $stmt->bindValue(':status', $status === 'Non-Aktif' ? 'Non-Aktif' : 'Aktif', PDO::PARAM_STR);
        $stmt->bindValue(':join_date', trim($join_date), PDO::PARAM_STR);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Anggota baru berhasil didaftarkan!'];
        }
        return ['success' => false, 'message' => 'Gagal mendaftarkan anggota.'];
    }

    /**
     * Memperbarui data anggota (UPDATE)
     */
    public function update($id, $member_code, $name, $email, $phone, $address, $status, $join_date) {
        if ($this->isCodeExists($member_code, $id)) {
            return ['success' => false, 'message' => "Kode anggota '{$member_code}' sudah digunakan oleh anggota lain!"];
        }

        if ($this->isEmailExists($email, $id)) {
            return ['success' => false, 'message' => "Email '{$email}' sudah digunakan oleh anggota lain!"];
        }

        $stmt = $this->db->prepare(
            "UPDATE members 
             SET member_code = :code, name = :name, email = :email, phone = :phone, 
                 address = :address, status = :status, join_date = :join_date 
             WHERE id = :id"
        );
        $stmt->bindValue(':code', strtoupper(trim($member_code)), PDO::PARAM_STR);
        $stmt->bindValue(':name', trim($name), PDO::PARAM_STR);
        $stmt->bindValue(':email', strtolower(trim($email)), PDO::PARAM_STR);
        $stmt->bindValue(':phone', trim($phone), PDO::PARAM_STR);
        $stmt->bindValue(':address', trim($address), PDO::PARAM_STR);
        $stmt->bindValue(':status', $status === 'Non-Aktif' ? 'Non-Aktif' : 'Aktif', PDO::PARAM_STR);
        $stmt->bindValue(':join_date', trim($join_date), PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Data anggota berhasil diperbarui!'];
        }
        return ['success' => false, 'message' => 'Gagal memperbarui data anggota.'];
    }

    /**
     * Menghapus anggota (DELETE)
     * Menguji integritas foreign key peminjaman
     */
    public function delete($id) {
        $activeLoans = $this->db->prepare(
            "SELECT COUNT(*) FROM loans WHERE member_id = :id AND status = 'Dipinjam'"
        );
        $activeLoans->bindValue(':id', $id, PDO::PARAM_INT);
        $activeLoans->execute();
        if ($activeLoans->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Anggota tidak dapat dihapus karena masih memiliki pinjaman buku aktif yang belum dikembalikan!'
            ];
        }

        $historyLoans = $this->db->prepare("SELECT COUNT(*) FROM loans WHERE member_id = :id");
        $historyLoans->bindValue(':id', $id, PDO::PARAM_INT);
        $historyLoans->execute();
        if ($historyLoans->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Anggota memiliki riwayat arsip peminjaman. Nonaktifkan status anggota daripada menghapusnya demi arsip.'
            ];
        }

        $stmt = $this->db->prepare("DELETE FROM members WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Anggota berhasil dihapus dari sistem!'];
        }
        return ['success' => false, 'message' => 'Gagal menghapus anggota.'];
    }

    /**
     * Menghitung total seluruh anggota
     */
    public function count() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM members");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Menghitung anggota dengan status aktif
     */
    public function countActive() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM members WHERE status = 'Aktif'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
?>

