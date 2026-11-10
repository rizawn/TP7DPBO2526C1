<?php
/**
 * View: Dashboard Overview
 * PustakaVerse - Sistem Manajemen Perpustakaan Modern
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 */
$pageTitle = "Dashboard Utama";

$totalCategories = $categoryModel->count();
$totalBooks = $bookModel->count();
$totalStock = $bookModel->sumStock();
$totalMembers = $memberModel->count();
$activeMembers = $memberModel->countActive();
$activeLoans = $loanModel->countActive();
$overdueLoans = $loanModel->countOverdue();
$recentLoans = array_slice($loanModel->getAll(), 0, 5);
$lowStockBooks = $bookModel->getLowStock(4);
?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h2 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-gauge-high text-primary me-2"></i>Dashboard Perpustakaan
        </h2>
        <p class="text-secondary mb-0">
            Sistem Informasi Pengelolaan Sirkulasi Koleksi Buku, Kategori, dan Keanggotaan Berbasis OOP PHP & GUI.
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="index.php?page=loans&action=create" class="btn btn-primary-custom shadow-sm">
            <i class="fa-solid fa-plus-circle me-1"></i> Transaksi Pinjam Baru
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card primary">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Katalog Buku</span>
                <div class="stat-icon primary">
                    <i class="fa-solid fa-book"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($totalBooks) ?></div>
            <div class="text-secondary small">
                Total Stok Fisik: <strong class="text-primary"><?= number_format($totalStock) ?></strong> eks.
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card info">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Kategori Koleksi</span>
                <div class="stat-icon info">
                    <i class="fa-solid fa-tags"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($totalCategories) ?></div>
            <div class="text-secondary small">
                Klasifikasi rak penataan buku
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card success">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Data Anggota</span>
                <div class="stat-icon success">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($totalMembers) ?></div>
            <div class="text-secondary small">
                Anggota Aktif: <strong class="text-success"><?= number_format($activeMembers) ?></strong> orang
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card <?= $overdueLoans > 0 ? 'danger' : 'warning' ?>">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="stat-label">Pinjaman Aktif</span>
                <div class="stat-icon <?= $overdueLoans > 0 ? 'danger' : 'warning' ?>">
                    <i class="fa-solid fa-handshake-simple"></i>
                </div>
            </div>
            <div class="stat-value"><?= number_format($activeLoans) ?></div>
            <div class="text-secondary small">
                <?php if ($overdueLoans > 0): ?>
                    <span class="text-danger fw-semibold"><i class="fa-solid fa-triangle-exclamation"></i> <?= $overdueLoans ?> Terlambat</span>
                <?php else: ?>
                    <span class="text-success fw-semibold"><i class="fa-solid fa-check"></i> Seluruhnya Tepat Waktu</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Loans -->
    <div class="col-lg-8">
        <div class="content-surface">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h5 class="fw-bold mb-0">
                    <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Sirkulasi Peminjaman Terkini
                </h5>
                <a href="index.php?page=loans" class="btn btn-sm btn-outline-primary">
                    Lihat Semua <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Buku</th>
                            <th>Peminjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLoans)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada aktivitas sirkulasi peminjaman.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentLoans as $row): ?>
                                <tr>
                                    <td>
                                        <span class="font-monospace fw-semibold text-primary"><?= htmlspecialchars($row['loan_code']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['book_title']) ?>">
                                            <?= htmlspecialchars($row['book_title']) ?>
                                        </div>
                                        <small class="text-muted"><?= htmlspecialchars($row['category_name']) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($row['member_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['member_code']) ?></small>
                                    </td>
                                    <td>
                                        <span class="small"><?= date('d M Y', strtotime($row['due_date'])) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'Dikembalikan'): ?>
                                            <span class="badge-custom badge-returned">
                                                <i class="fa-solid fa-check"></i> Dikembalikan
                                            </span>
                                        <?php elseif ($row['dynamic_status'] === 'Terlambat'): ?>
                                            <span class="badge-custom badge-overdue">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Terlambat (+<?= $row['days_late'] ?> hr)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-custom badge-borrowed">
                                                <i class="fa-solid fa-hourglass-half"></i> Dipinjam
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['status'] !== 'Dikembalikan'): ?>
                                            <a href="index.php?page=loans&action=return&id=<?= $row['id'] ?>" 
                                               class="btn btn-sm btn-success px-2 py-1" 
                                               title="Kembalikan Buku"
                                               onclick="return confirm('Proses pengembalian buku ini sekarang?')">
                                                <i class="fa-solid fa-rotate-left me-1"></i> Kembali
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="fa-solid fa-circle-check text-success"></i> Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Side: Low Stock & Quick Info -->
    <div class="col-lg-4">
        <!-- Low Stock Alert -->
        <div class="content-surface mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-danger">
                    <i class="fa-solid fa-boxes-stacked me-2"></i>Peringatan Stok Rendah
                </h6>
                <span class="badge bg-danger rounded-pill"><?= count($lowStockBooks) ?> Judul</span>
            </div>

            <?php if (empty($lowStockBooks)): ?>
                <div class="text-center py-3 text-muted small">
                    <i class="fa-solid fa-circle-check text-success fs-4 d-block mb-1"></i>
                    Semua stok buku dalam jumlah aman.
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($lowStockBooks as $b): ?>
                        <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0 border-bottom">
                            <div class="me-2 text-truncate">
                                <div class="fw-semibold text-truncate small" title="<?= htmlspecialchars($b['title']) ?>">
                                    <?= htmlspecialchars($b['title']) ?>
                                </div>
                                <span class="text-muted smaller"><?= htmlspecialchars($b['author']) ?></span>
                            </div>
                            <span class="badge <?= $b['stock'] == 0 ? 'bg-danger' : 'bg-warning text-dark' ?> rounded-pill px-2 py-1">
                                Sisa <?= $b['stock'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- System Architecture Information Card -->
        <div class="content-surface bg-light border-0">
            <h6 class="fw-bold mb-2 text-dark">
                <i class="fa-solid fa-circle-info text-primary me-2"></i>Spesifikasi OOP & GUI
            </h6>
            <ul class="list-unstyled small text-secondary mb-0">
                <li class="mb-2">
                    <i class="fa-solid fa-check text-success me-2"></i><strong>Arsitektur Modular:</strong> Pemisahan Class, Config, Database, dan View.
                </li>
                <li class="mb-2">
                    <i class="fa-solid fa-check text-success me-2"></i><strong>Prepared Statement:</strong> Seluruh query PDO 100% aman dari SQL Injection.
                </li>
                <li class="mb-2">
                    <i class="fa-solid fa-check text-success me-2"></i><strong>Foreign Key Relasional:</strong> Relasi ganda antar tabel terikat integritas referensial.
                </li>
                <li>
                    <i class="fa-solid fa-check text-success me-2"></i><strong>Atomic Stock Management:</strong> Otomatisasi penyesuaian stok saat pinjam/kembali.
                </li>
            </ul>
        </div>
    </div>
</div>

