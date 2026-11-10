<?php
/**
 * View: Manajemen Sirkulasi Peminjaman & Pengembalian Buku (CRUD)
 * PustakaVerse - Sistem Manajemen Perpustakaan Modern
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 */
$pageTitle = "Sirkulasi Peminjaman Buku";
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';

$loans = $loanModel->getAll($searchKeyword, $filterStatus);
$availableBooks = $bookModel->getAll();
$members = $memberModel->getAll();

// Ambil data untuk modal pengembalian jika ada parameter return
$returnModalLoan = null;
if (isset($_GET['action']) && $_GET['action'] === 'return_modal' && isset($_GET['id'])) {
    $returnModalLoan = $loanModel->getById($_GET['id']);
}
?>

<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-handshake-simple text-primary me-2"></i>Sirkulasi Peminjaman & Pengembalian
        </h2>
        <p class="text-secondary mb-0">
            Pencatatan peminjaman, pelacakan jatuh tempo, denda keterlambatan, dan sinkronisasi stok otomatis.
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <button type="button" class="btn btn-primary-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPinjam">
            <i class="fa-solid fa-plus-circle me-1"></i> Transaksi Pinjam Baru
        </button>
    </div>
</div>

<div class="content-surface">
    <!-- Filter Toolbar & Tabs -->
    <div class="row g-3 justify-content-between align-items-center mb-4">
        <div class="col-lg-7">
            <div class="btn-group me-2 mb-2 mb-lg-0" role="group">
                <a href="index.php?page=loans" class="btn btn-sm <?= empty($filterStatus) ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Semua Status
                </a>
                <a href="index.php?page=loans&status=Dipinjam" class="btn btn-sm <?= $filterStatus === 'Dipinjam' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    <i class="fa-solid fa-hourglass-half me-1"></i> Dipinjam
                </a>
                <a href="index.php?page=loans&status=Terlambat" class="btn btn-sm <?= $filterStatus === 'Terlambat' ? 'btn-danger' : 'btn-outline-danger' ?>">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Terlambat
                </a>
                <a href="index.php?page=loans&status=Dikembalikan" class="btn btn-sm <?= $filterStatus === 'Dikembalikan' ? 'btn-success' : 'btn-outline-success' ?>">
                    <i class="fa-solid fa-check me-1"></i> Selesai
                </a>
            </div>
        </div>

        <div class="col-lg-5">
            <form method="GET" action="index.php" class="d-flex gap-2">
                <input type="hidden" name="page" value="loans">
                <?php if (!empty($filterStatus)): ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
                <?php endif; ?>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 bg-light" 
                           placeholder="Cari kode, judul buku, anggota..." 
                           value="<?= htmlspecialchars($searchKeyword) ?>">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>
                <?php if (!empty($searchKeyword) || !empty($filterStatus)): ?>
                    <a href="index.php?page=loans" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Circulation Table -->
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode Pinjam</th>
                    <th>Buku yang Dipinjam</th>
                    <th>Anggota Peminjam</th>
                    <th>Tgl Pinjam</th>
                    <th>Jatuh Tempo</th>
                    <th>Tgl Kembali</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Denda</th>
                    <th class="text-center" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($loans)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-handshake-slash fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Tidak ada data transaksi peminjaman yang sesuai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($loans as $l): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <span class="badge bg-light text-primary border font-monospace px-2 py-1">
                                    <?= htmlspecialchars($l['loan_code']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($l['book_title']) ?>">
                                    <?= htmlspecialchars($l['book_title']) ?>
                                </div>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary smaller">
                                    <?= htmlspecialchars($l['category_name']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($l['member_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($l['member_code']) ?></small>
                            </td>
                            <td>
                                <span class="small text-secondary"><?= date('d/m/Y', strtotime($l['loan_date'])) ?></span>
                            </td>
                            <td>
                                <span class="small fw-semibold <?= ($l['dynamic_status'] === 'Terlambat') ? 'text-danger' : 'text-dark' ?>">
                                    <?= date('d/m/Y', strtotime($l['due_date'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($l['return_date'])): ?>
                                    <span class="small text-success"><?= date('d/m/Y', strtotime($l['return_date'])) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">Belum kembali</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($l['status'] === 'Dikembalikan'): ?>
                                    <span class="badge-custom badge-returned">
                                        <i class="fa-solid fa-check"></i> Dikembalikan
                                    </span>
                                <?php elseif ($l['dynamic_status'] === 'Terlambat'): ?>
                                    <span class="badge-custom badge-overdue" title="Terlambat <?= $l['days_late'] ?> hari">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Terlambat (+<?= $l['days_late'] ?> hr)
                                    </span>
                                <?php else: ?>
                                    <span class="badge-custom badge-borrowed">
                                        <i class="fa-solid fa-hourglass-half"></i> Dipinjam
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($l['calculated_fine'] > 0): ?>
                                    <span class="fw-bold text-danger">Rp <?= number_format($l['calculated_fine'], 0, ',', '.') ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">Rp 0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <?php if ($l['status'] !== 'Dikembalikan'): ?>
                                        <button type="button" 
                                                class="btn btn-action btn-outline-success btn-return-loan" 
                                                title="Kembalikan Buku"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalKembalikanBuku"
                                                data-id="<?= $l['id'] ?>"
                                                data-code="<?= htmlspecialchars($l['loan_code']) ?>"
                                                data-book="<?= htmlspecialchars($l['book_title']) ?>"
                                                data-member="<?= htmlspecialchars($l['member_name']) ?>"
                                                data-duedate="<?= $l['due_date'] ?>"
                                                data-fine="<?= $l['calculated_fine'] ?>">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </button>
                                    <?php endif; ?>

                                    <!-- Edit Modal Trigger -->
                                    <button type="button" 
                                            class="btn btn-action btn-outline-warning btn-edit-loan" 
                                            title="Edit Peminjaman"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditPinjam"
                                            data-id="<?= $l['id'] ?>"
                                            data-code="<?= htmlspecialchars($l['loan_code']) ?>"
                                            data-duedate="<?= $l['due_date'] ?>"
                                            data-notes="<?= htmlspecialchars($l['notes'] ?: '') ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- Delete Trigger -->
                                    <a href="index.php?page=loans&action=delete&id=<?= $l['id'] ?>" 
                                       class="btn btn-action btn-outline-danger" 
                                       title="Hapus Catatan"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus catatan transaksi <?= htmlspecialchars($l['loan_code']) ?>?')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Peminjaman (Borrow) -->
<div class="modal fade" id="modalTambahPinjam" tabindex="-1" aria-labelledby="modalTambahPinjamLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php?page=loans&action=borrow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahPinjamLabel">
                        <i class="fa-solid fa-handshake-simple text-primary me-2"></i>Transaksi Peminjaman Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Pilih Buku yang Dipinjam <span class="text-danger">*</span></label>
                            <select name="book_id" class="form-select" required>
                                <option value="">-- Pilih Koleksi Buku --</option>
                                <?php foreach ($availableBooks as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= ($b['stock'] <= 0) ? 'disabled class="text-muted"' : '' ?>>
                                        <?= htmlspecialchars($b['title']) ?> (Stok: <?= $b['stock'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Buku dengan stok 0 tidak dapat dipinjam.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Pilih Anggota Peminjam <span class="text-danger">*</span></label>
                            <select name="member_id" class="form-select" required>
                                <option value="">-- Pilih Anggota Terdaftar --</option>
                                <?php foreach ($members as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= ($m['status'] !== 'Aktif') ? 'disabled class="text-muted"' : '' ?>>
                                        <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['member_code']) ?>) - <?= $m['status'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hanya anggota berstatus aktif yang dapat meminjam.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Tanggal Pinjam <span class="text-danger">*</span></label>
                            <input type="date" name="loan_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Batas Jatuh Tempo <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                            <small class="text-muted">Standar durasi pinjam adalah 7 hari.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Catatan Tambahan (Opsional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Catatan kondisi buku atau keperluan pinjam..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit_borrow" class="btn btn-primary-custom">Catat Peminjaman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Pengembalian Buku -->
<div class="modal fade" id="modalKembalikanBuku" tabindex="-1" aria-labelledby="modalKembalikanBukuLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php?page=loans&action=return">
                <input type="hidden" name="id" id="return_loan_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalKembalikanBukuLabel">
                        <i class="fa-solid fa-rotate-left text-success me-2"></i>Konfirmasi Pengembalian Buku
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="bg-light p-3 rounded mb-3">
                        <div class="small text-muted mb-1">Kode Transaksi: <strong id="return_loan_code" class="text-primary font-monospace"></strong></div>
                        <div class="fw-bold text-dark mb-1" id="return_book_title"></div>
                        <div class="small text-secondary">Peminjam: <span id="return_member_name"></span></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Tanggal Dikembalikan <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Kondisi / Catatan Pengembalian</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Buku dikembalikan dalam kondisi baik dan lengkap."></textarea>
                    </div>

                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Stok eksemplar buku akan bertambah <strong>+1</strong> secara otomatis setelah proses ini disimpan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="confirm_return" class="btn btn-success fw-semibold">Proses Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Peminjaman -->
<div class="modal fade" id="modalEditPinjam" tabindex="-1" aria-labelledby="modalEditPinjamLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php?page=loans&action=update">
                <input type="hidden" name="id" id="edit_loan_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalEditPinjamLabel">
                        <i class="fa-solid fa-pen-to-square text-warning me-2"></i>Perbarui Data Peminjaman
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Kode Transaksi</label>
                        <input type="text" id="edit_loan_code" class="form-control bg-light font-monospace" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Perpanjang Batas Jatuh Tempo <span class="text-danger">*</span></label>
                        <input type="date" name="due_date" id="edit_loan_duedate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Catatan Transaksi</label>
                        <textarea name="notes" id="edit_loan_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_loan" class="btn btn-warning text-dark fw-semibold">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Return Modal Listener
    const returnButtons = document.querySelectorAll('.btn-return-loan');
    returnButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('return_loan_id').value = this.dataset.id;
            document.getElementById('return_loan_code').innerText = this.dataset.code;
            document.getElementById('return_book_title').innerText = this.dataset.book;
            document.getElementById('return_member_name').innerText = this.dataset.member;
        });
    });

    // Edit Loan Modal Listener
    const editLoanButtons = document.querySelectorAll('.btn-edit-loan');
    editLoanButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_loan_id').value = this.dataset.id;
            document.getElementById('edit_loan_code').value = this.dataset.code;
            document.getElementById('edit_loan_duedate').value = this.dataset.duedate;
            document.getElementById('edit_loan_notes').value = this.dataset.notes;
        });
    });
});
</script>

