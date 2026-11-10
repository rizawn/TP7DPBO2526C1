<?php
/**
 * Konfigurasi Koneksi Database Menggunakan PDO (PHP Data Objects)
 * Modul 8: OOP dalam Website PHP dengan GUI
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 */

class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "db_pustaka_tp7";
    public $conn;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>

