<?php
/**
 * View: Manajemen Data Anggota (CRUD)
 * PustakaVerse - Sistem Manajemen Perpustakaan Modern
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 */
$pageTitle = "Manajemen Data Anggota";
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterStatus = isset($_GET['status']) ? trim($_GET['status']) : '';

$members = $memberModel->getAll($searchKeyword, $filterStatus);
?>

<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-users text-primary me-2"></i>Data Keanggotaan Perpustakaan
        </h2>
        <p class="text-secondary mb-0">
            Kelola data peminjam, nomor identitas keanggotaan, kontak, dan status keaktifan.
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <button type="button" class="btn btn-primary-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahAnggota">
            <i class="fa-solid fa-user-plus me-1"></i> Daftarkan Anggota Baru
        </button>
    </div>
</div>

<div class="content-surface">
    <!-- Filter & Search Toolbar -->
    <form method="GET" action="index.php" class="row g-2 align-items-center mb-4">
        <input type="hidden" name="page" value="members">

        <div class="col-md-5 col-lg-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" 
                       placeholder="Cari kode, nama, email, no telp..." 
                       value="<?= htmlspecialchars($searchKeyword) ?>">
            </div>
        </div>

        <div class="col-md-4 col-lg-3">
            <select name="status" class="form-select bg-light" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="Aktif" <?= $filterStatus === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="Non-Aktif" <?= $filterStatus === 'Non-Aktif' ? 'selected' : '' ?>>Non-Aktif</option>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Filter</button>
            <?php if (!empty($searchKeyword) || !empty($filterStatus)): ?>
                <a href="index.php?page=members" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="col-12 col-md text-md-end text-muted small mt-2 mt-md-0">
            Total Anggota: <strong><?= count($members) ?></strong> orang
        </div>
    </form>

    <!-- Table Anggota -->
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Kode Anggota</th>
                    <th>Nama Lengkap</th>
                    <th>Kontak (Email & No. HP)</th>
                    <th>Alamat Domisili</th>
                    <th>Tgl Bergabung</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Pinjaman Aktif</th>
                    <th class="text-center" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-user-slash fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Tidak ada data anggota yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($members as $m): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <span class="badge bg-light text-primary border font-monospace px-2 py-1">
                                    <?= htmlspecialchars($m['member_code']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($m['name']) ?></div>
                            </td>
                            <td>
                                <div class="small text-dark">
                                    <i class="fa-solid fa-envelope text-muted me-1"></i><?= htmlspecialchars($m['email']) ?>
                                </div>
                                <div class="small text-muted">
                                    <i class="fa-solid fa-phone text-muted me-1"></i><?= htmlspecialchars($m['phone']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($m['address']) ?>">
                                    <?= htmlspecialchars($m['address']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="small text-secondary"><?= date('d M Y', strtotime($m['join_date'])) ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($m['status'] === 'Aktif'): ?>
                                    <span class="badge-custom badge-active">
                                        <i class="fa-solid fa-circle-check"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge-custom badge-inactive">
                                        <i class="fa-solid fa-circle-xmark"></i> Non-Aktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($m['active_loans'] > 0): ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1">
                                        <?= $m['active_loans'] ?> Buku
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1">
                                        0 Buku
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" 
                                            class="btn btn-action btn-outline-warning btn-edit-member" 
                                            title="Edit Anggota"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditAnggota"
                                            data-id="<?= $m['id'] ?>"
                                            data-code="<?= htmlspecialchars($m['member_code']) ?>"
                                            data-name="<?= htmlspecialchars($m['name']) ?>"
                                            data-email="<?= htmlspecialchars($m['email']) ?>"
                                            data-phone="<?= htmlspecialchars($m['phone']) ?>"
                                            data-address="<?= htmlspecialchars($m['address']) ?>"
                                            data-status="<?= htmlspecialchars($m['status']) ?>"
                                            data-joindate="<?= htmlspecialchars($m['join_date']) ?>">
                                        <i class="fa-solid fa-user-pen"></i>
                                    </button>
                                    <a href="index.php?page=members&action=delete&id=<?= $m['id'] ?>" 
                                       class="btn btn-action btn-outline-danger" 
                                       title="Hapus Anggota"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus data anggota \'<?= htmlspecialchars(addslashes($m['name'])) ?>\'?')">
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

<!-- Modal Tambah Anggota -->
<div class="modal fade" id="modalTambahAnggota" tabindex="-1" aria-labelledby="modalTambahAnggotaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php?page=members&action=create">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahAnggotaLabel">
                        <i class="fa-solid fa-user-plus text-primary me-2"></i>Daftarkan Anggota Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Kode Anggota <span class="text-danger">*</span></label>
                        <input type="text" name="member_code" class="form-control" placeholder="Contoh: MBR-2026-005" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Nama Lengkap Anggota <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Muhammad Ihsan" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="email@domain.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Alamat Domisili <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Alamat tempat tinggal..." required></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Status Keanggotaan <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="Aktif" selected>Aktif</option>
                                <option value="Non-Aktif">Non-Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Tanggal Bergabung <span class="text-danger">*</span></label>
                            <input type="date" name="join_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit_member" class="btn btn-primary-custom">Daftarkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Anggota -->
<div class="modal fade" id="modalEditAnggota" tabindex="-1" aria-labelledby="modalEditAnggotaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php?page=members&action=update">
                <input type="hidden" name="id" id="edit_member_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalEditAnggotaLabel">
                        <i class="fa-solid fa-user-pen text-warning me-2"></i>Perbarui Data Anggota
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Kode Anggota <span class="text-danger">*</span></label>
                        <input type="text" name="member_code" id="edit_member_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Nama Lengkap Anggota <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_member_name" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_member_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Nomor WhatsApp / HP <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="edit_member_phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Alamat Domisili <span class="text-danger">*</span></label>
                        <textarea name="address" id="edit_member_address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Status Keanggotaan <span class="text-danger">*</span></label>
                            <select name="status" id="edit_member_status" class="form-select" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Non-Aktif">Non-Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Tanggal Bergabung <span class="text-danger">*</span></label>
                            <input type="date" name="join_date" id="edit_member_joindate" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_member" class="btn btn-warning text-dark fw-semibold">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.btn-edit-member');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_member_id').value = this.dataset.id;
            document.getElementById('edit_member_code').value = this.dataset.code;
            document.getElementById('edit_member_name').value = this.dataset.name;
            document.getElementById('edit_member_email').value = this.dataset.email;
            document.getElementById('edit_member_phone').value = this.dataset.phone;
            document.getElementById('edit_member_address').value = this.dataset.address;
            document.getElementById('edit_member_status').value = this.dataset.status;
            document.getElementById('edit_member_joindate').value = this.dataset.joindate;
        });
    });
});
</script>

