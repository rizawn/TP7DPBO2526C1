<?php
/**
 * Header Modular Template
 * PustakaVerse - Sistem Manajemen Perpustakaan Modern
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 */
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>PustakaVerse DPBO</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand" href="index.php?page=dashboard">
                <i class="fa-solid fa-book-bookmark"></i>
                <span>PustakaVerse</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
                            <i class="fa-solid fa-chart-pie me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'categories' ? 'active' : '' ?>" href="index.php?page=categories">
                            <i class="fa-solid fa-tags me-1"></i> Kategori Buku
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'books' ? 'active' : '' ?>" href="index.php?page=books">
                            <i class="fa-solid fa-book me-1"></i> Katalog Buku
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'members' ? 'active' : '' ?>" href="index.php?page=members">
                            <i class="fa-solid fa-users me-1"></i> Data Anggota
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'loans' ? 'active' : '' ?>" href="index.php?page=loans">
                            <i class="fa-solid fa-handshake-simple me-1"></i> Sirkulasi Peminjaman
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center text-light gap-2">
                    <span class="badge bg-primary bg-opacity-50 text-white border border-light border-opacity-25 py-2 px-3 rounded-pill">
                        <i class="fa-solid fa-user-graduate me-1 text-info"></i>
                        <strong>Riza Wahyu Nugraha</strong> &bull; 2511421
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container-fluid px-lg-5 py-4 flex-grow-1">

        <!-- Flash Message Notification -->
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?> alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm border-0" role="alert">
                <i class="fa-solid <?= $_SESSION['flash']['type'] === 'success' ? 'fa-circle-check text-success' : 'fa-circle-exclamation text-danger' ?> fs-4 me-3"></i>
                <div class="flex-grow-1 fw-medium">
                    <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

