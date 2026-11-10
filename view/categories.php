<?php
/**
 * View: Manajemen Kategori Buku (CRUD)
 * PustakaVerse - Sistem Manajemen Perpustakaan Modern
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 */
$pageTitle = "Manajemen Kategori Buku";
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$categories = $categoryModel->getAll($searchKeyword);
?>

<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-tags text-primary me-2"></i>Kategori Koleksi Buku
        </h2>
        <p class="text-secondary mb-0">
            Kelola klasifikasi buku, lokasi penataan rak, dan deskripsi kategori (Entitas Master).
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <button type="button" class="btn btn-primary-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
            <i class="fa-solid fa-plus-circle me-1"></i> Tambah Kategori Baru
        </button>
    </div>
</div>

<div class="content-surface">
    <!-- Filter & Search Toolbar -->
    <div class="row g-3 justify-content-between align-items-center mb-4">
        <div class="col-md-6 col-lg-5">
            <form method="GET" action="index.php" class="d-flex gap-2">
                <input type="hidden" name="page" value="categories">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 bg-light" 
                           placeholder="Cari kode, nama, atau rak..." 
                           value="<?= htmlspecialchars($searchKeyword) ?>">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>
                <?php if (!empty($searchKeyword)): ?>
                    <a href="index.php?page=categories" class="btn btn-outline-secondary" title="Reset Pencarian">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>
        <div class="col-md-4 text-md-end text-muted small">
            Menampilkan <strong><?= count($categories) ?></strong> kategori
        </div>
    </div>

    <!-- Table Kategori -->
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Kode</th>
                    <th>Nama Kategori</th>
                    <th>Lokasi Rak</th>
                    <th>Deskripsi</th>
                    <th class="text-center">Total Buku</th>
                    <th class="text-center" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Tidak ada data kategori yang sesuai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <span class="badge bg-light text-primary border font-monospace px-2 py-1">
                                    <?= htmlspecialchars($cat['code']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($cat['name']) ?></div>
                            </td>
                            <td>
                                <span class="text-secondary small">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i><?= htmlspecialchars($cat['shelf_location']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-muted small text-truncate" style="max-width: 320px;" title="<?= htmlspecialchars($cat['description']) ?>">
                                    <?= htmlspecialchars($cat['description'] ?: '-') ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold rounded-pill px-3 py-1">
                                    <?= $cat['total_books'] ?> Buku
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" 
                                            class="btn btn-action btn-outline-warning btn-edit-category" 
                                            title="Edit Kategori"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditKategori"
                                            data-id="<?= $cat['id'] ?>"
                                            data-code="<?= htmlspecialchars($cat['code']) ?>"
                                            data-name="<?= htmlspecialchars($cat['name']) ?>"
                                            data-shelf="<?= htmlspecialchars($cat['shelf_location']) ?>"
                                            data-desc="<?= htmlspecialchars($cat['description']) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <a href="index.php?page=categories&action=delete&id=<?= $cat['id'] ?>" 
                                       class="btn btn-action btn-outline-danger" 
                                       title="Hapus Kategori"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus kategori \'<?= htmlspecialchars(addslashes($cat['name'])) ?>\'?')">
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

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-labelledby="modalTambahKategoriLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php?page=categories&action=create">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahKategoriLabel">
                        <i class="fa-solid fa-plus-circle text-primary me-2"></i>Tambah Kategori Buku
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Kode Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="Contoh: CAT-AI" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Kecerdasan Buatan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Lokasi Rak Penyimpanan <span class="text-danger">*</span></label>
                        <input type="text" name="shelf_location" class="form-control" placeholder="Contoh: Rak C-05" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Deskripsi Kategori</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Uraian singkat topik kategori..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit_category" class="btn btn-primary-custom">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal fade" id="modalEditKategori" tabindex="-1" aria-labelledby="modalEditKategoriLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php?page=categories&action=update">
                <input type="hidden" name="id" id="edit_category_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalEditKategoriLabel">
                        <i class="fa-solid fa-pen-to-square text-warning me-2"></i>Perbarui Data Kategori
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Kode Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="edit_category_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_category_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Lokasi Rak Penyimpanan <span class="text-danger">*</span></label>
                        <input type="text" name="shelf_location" id="edit_category_shelf" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Deskripsi Kategori</label>
                        <textarea name="description" id="edit_category_desc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_category" class="btn btn-warning text-dark fw-semibold">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.btn-edit-category');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_category_id').value = this.dataset.id;
            document.getElementById('edit_category_code').value = this.dataset.code;
            document.getElementById('edit_category_name').value = this.dataset.name;
            document.getElementById('edit_category_shelf').value = this.dataset.shelf;
            document.getElementById('edit_category_desc').value = this.dataset.desc;
        });
    });
});
</script>

