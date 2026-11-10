<?php
/**
 * Titik Masuk Utama (Controller / Router)
 * PustakaVerse - Sistem Manajemen Perpustakaan Berorientasi Objek
 * Modul 8: OOP dalam Website PHP dengan GUI
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 * Kelas: Desain dan Pemrograman Berorientasi Objek (DPBO) 2026
 */

session_start();

// Load Class Model Entitas OOP
require_once __DIR__ . '/class/Category.php';
require_once __DIR__ . '/class/Book.php';
require_once __DIR__ . '/class/Member.php';
require_once __DIR__ . '/class/Loan.php';

// Inisialisasi Objek Model
$categoryModel = new Category();
$bookModel     = new Book();
$memberModel   = new Member();
$loanModel     = new Loan();

$page   = isset($_GET['page']) ? trim($_GET['page']) : 'dashboard';
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

// =========================================================================
// CONTROLLER: Penanganan Request Aksi CRUD (POST & GET)
// =========================================================================

// --- 1. Aksi Entitas Kategori (categories) ---
if ($page === 'categories') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
        $res = $categoryModel->create($_POST['code'], $_POST['name'], $_POST['shelf_location'], $_POST['description'] ?? '');
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=categories");
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
        $res = $categoryModel->update((int)$_POST['id'], $_POST['code'], $_POST['name'], $_POST['shelf_location'], $_POST['description'] ?? '');
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=categories");
        exit;
    }
    if ($action === 'delete' && isset($_GET['id'])) {
        $res = $categoryModel->delete((int)$_GET['id']);
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=categories");
        exit;
    }
}

// --- 2. Aksi Entitas Buku (books) ---
if ($page === 'books') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
        $res = $bookModel->create(
            $_POST['isbn'], $_POST['title'], $_POST['author'], $_POST['publisher'],
            (int)$_POST['year'], (int)$_POST['category_id'], (int)$_POST['stock'],
            $_POST['cover_url'] ?? '', $_POST['synopsis'] ?? ''
        );
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=books");
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
        $res = $bookModel->update(
            (int)$_POST['id'], $_POST['isbn'], $_POST['title'], $_POST['author'], $_POST['publisher'],
            (int)$_POST['year'], (int)$_POST['category_id'], (int)$_POST['stock'],
            $_POST['cover_url'] ?? '', $_POST['synopsis'] ?? ''
        );
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=books");
        exit;
    }
    if ($action === 'delete' && isset($_GET['id'])) {
        $res = $bookModel->delete((int)$_GET['id']);
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=books");
        exit;
    }
}

// --- 3. Aksi Entitas Anggota (members) ---
if ($page === 'members') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
        $res = $memberModel->create(
            $_POST['member_code'], $_POST['name'], $_POST['email'], $_POST['phone'],
            $_POST['address'], $_POST['status'] ?? 'Aktif', $_POST['join_date'] ?? date('Y-m-d')
        );
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=members");
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
        $res = $memberModel->update(
            (int)$_POST['id'], $_POST['member_code'], $_POST['name'], $_POST['email'], $_POST['phone'],
            $_POST['address'], $_POST['status'] ?? 'Aktif', $_POST['join_date']
        );
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=members");
        exit;
    }
    if ($action === 'delete' && isset($_GET['id'])) {
        $res = $memberModel->delete((int)$_GET['id']);
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=members");
        exit;
    }
}

// --- 4. Aksi Entitas Peminjaman (loans) ---
if ($page === 'loans') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'borrow') {
        $res = $loanModel->borrow(
            (int)$_POST['book_id'], (int)$_POST['member_id'],
            $_POST['loan_date'], $_POST['due_date'], $_POST['notes'] ?? ''
        );
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=loans");
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'return') {
        $res = $loanModel->returnBook(
            (int)$_POST['id'], $_POST['return_date'] ?? date('Y-m-d'), $_POST['notes'] ?? ''
        );
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=loans");
        exit;
    }
    if ($action === 'return' && isset($_GET['id'])) {
        // Quick return dari halaman dashboard / tabel
        $res = $loanModel->returnBook((int)$_GET['id'], date('Y-m-d'), 'Pengembalian Cepat');
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php?page=loans';
        header("Location: " . $back);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
        $res = $loanModel->update((int)$_POST['id'], $_POST['due_date'], $_POST['notes'] ?? '');
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=loans");
        exit;
    }
    if ($action === 'delete' && isset($_GET['id'])) {
        $res = $loanModel->delete((int)$_GET['id']);
        $_SESSION['flash'] = ['type' => $res['success'] ? 'success' : 'danger', 'message' => $res['message']];
        header("Location: index.php?page=loans");
        exit;
    }
}

// =========================================================================
// VIEW DISPATCHER: Tampilkan Antarmuka GUI
// =========================================================================

include 'view/header.php';

switch ($page) {
    case 'categories':
        include 'view/categories.php';
        break;
    case 'books':
        include 'view/books.php';
        break;
    case 'members':
        include 'view/members.php';
        break;
    case 'loans':
        include 'view/loans.php';
        break;
    case 'dashboard':
    default:
        include 'view/dashboard.php';
        break;
}

include 'view/footer.php';
?>

