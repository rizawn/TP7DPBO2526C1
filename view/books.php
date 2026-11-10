<?php
/**
 * View: Manajemen Katalog Buku (CRUD)
 * PustakaVerse - Sistem Manajemen Perpustakaan Modern
 * 
 * Nama : Riza Wahyu Nugraha
 * NIM  : 2511421
 */
$pageTitle = "Manajemen Katalog Buku";
$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterCategory = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

$books = $bookModel->getAll($searchKeyword, $filterCategory);
$categories = $categoryModel->getAll();
?>

<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-book text-primary me-2"></i>Katalog Koleksi Buku
        </h2>
        <p class="text-secondary mb-0">
            Kelola inventaris judul buku, nomor ISBN, stok eksemplar fisik, dan relasi kategori.
        </p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <button type="button" class="btn btn-primary-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBuku">
            <i class="fa-solid fa-plus-circle me-1"></i> Tambah Buku Baru
        </button>
    </div>
</div>

<div class="content-surface">
    <!-- Filter & Search Toolbar -->
    <form method="GET" action="index.php" class="row g-2 align-items-center mb-4">
        <input type="hidden" name="page" value="books">
        
        <div class="col-md-5 col-lg-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" 
                       placeholder="Cari judul, penulis, ISBN..." 
                       value="<?= htmlspecialchars($searchKeyword) ?>">
            </div>
        </div>

        <div class="col-md-4 col-lg-3">
            <select name="category_id" class="form-select bg-light" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $filterCategory == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Filter</button>
            <?php if (!empty($searchKeyword) || !empty($filterCategory)): ?>
                <a href="index.php?page=books" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="col-12 col-md text-md-end text-muted small mt-2 mt-md-0">
            Total Ditemukan: <strong><?= count($books) ?></strong> buku
        </div>
    </form>

    <!-- Table Buku -->
    <div class="table-responsive">
        <table class="table table-custom align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th style="width: 70px;">Cover</th>
                    <th>Judul & Penulis</th>
                    <th>Kategori</th>
                    <th>ISBN / Tahun</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($books)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-book-open fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Tidak ada koleksi buku yang sesuai dengan kriteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($books as $b): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?php if (!empty($b['cover_url'])): ?>
                                    <img src="<?= htmlspecialchars($b['cover_url']) ?>" alt="Cover" class="book-thumb" onerror="this.src='https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=100'">
                                <?php else: ?>
                                    <div class="book-thumb-empty"><i class="fa-solid fa-book"></i></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold text-dark text-truncate" style="max-width: 260px;" title="<?= htmlspecialchars($b['title']) ?>">
                                    <?= htmlspecialchars($b['title']) ?>
                                </div>
                                <div class="text-muted small">
                                    <i class="fa-solid fa-user-pen me-1"></i><?= htmlspecialchars($b['author']) ?>
                                </div>
                                <small class="text-secondary"><?= htmlspecialchars($b['publisher']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <?= htmlspecialchars($b['category_name']) ?>
                                </span>
                                <div class="text-muted smaller mt-1">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i><?= htmlspecialchars($b['shelf_location']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="font-monospace small"><?= htmlspecialchars($b['isbn']) ?></div>
                                <span class="badge bg-secondary bg-opacity-25 text-dark small"><?= $b['year'] ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($b['stock'] > 3): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1 rounded-pill">
                                        <?= $b['stock'] ?> eks
                                    </span>
                                <?php elseif ($b['stock'] > 0): ?>
                                    <span class="badge bg-warning bg-opacity-25 text-dark fw-bold px-3 py-1 rounded-pill">
                                        <?= $b['stock'] ?> eks
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger text-white fw-bold px-3 py-1 rounded-pill">
                                        Habis
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- Detail Modal Trigger -->
                                    <button type="button" 
                                            class="btn btn-action btn-outline-info btn-detail-book" 
                                            title="Detail Buku"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetailBuku"
                                            data-title="<?= htmlspecialchars($b['title']) ?>"
                                            data-author="<?= htmlspecialchars($b['author']) ?>"
                                            data-publisher="<?= htmlspecialchars($b['publisher']) ?>"
                                            data-year="<?= $b['year'] ?>"
                                            data-isbn="<?= htmlspecialchars($b['isbn']) ?>"
                                            data-category="<?= htmlspecialchars($b['category_name']) ?>"
                                            data-shelf="<?= htmlspecialchars($b['shelf_location']) ?>"
                                            data-stock="<?= $b['stock'] ?>"
                                            data-cover="<?= htmlspecialchars($b['cover_url']) ?>"
                                            data-synopsis="<?= htmlspecialchars($b['synopsis'] ?: 'Sinopsis belum tersedia untuk judul buku ini.') ?>">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <!-- Edit Modal Trigger -->
                                    <button type="button" 
                                            class="btn btn-action btn-outline-warning btn-edit-book" 
                                            title="Edit Buku"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditBuku"
                                            data-id="<?= $b['id'] ?>"
                                            data-isbn="<?= htmlspecialchars($b['isbn']) ?>"
                                            data-title="<?= htmlspecialchars($b['title']) ?>"
                                            data-author="<?= htmlspecialchars($b['author']) ?>"
                                            data-publisher="<?= htmlspecialchars($b['publisher']) ?>"
                                            data-year="<?= $b['year'] ?>"
                                            data-catid="<?= $b['category_id'] ?>"
                                            data-stock="<?= $b['stock'] ?>"
                                            data-cover="<?= htmlspecialchars($b['cover_url']) ?>"
                                            data-synopsis="<?= htmlspecialchars($b['synopsis']) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <!-- Delete Trigger -->
                                    <a href="index.php?page=books&action=delete&id=<?= $b['id'] ?>" 
                                       class="btn btn-action btn-outline-danger" 
                                       title="Hapus Buku"
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus buku \'<?= htmlspecialchars(addslashes($b['title'])) ?>\'?')">
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

<!-- Modal Tambah Buku -->
<div class="modal fade" id="modalTambahBuku" tabindex="-1" aria-labelledby="modalTambahBukuLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php?page=books&action=create">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahBukuLabel">
                        <i class="fa-solid fa-plus-circle text-primary me-2"></i>Tambah Koleksi Buku Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Nomor ISBN <span class="text-danger">*</span></label>
                            <input type="text" name="isbn" class="form-control" placeholder="Contoh: 9780132350884" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Kategori Buku <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?> (<?= htmlspecialchars($cat['shelf_location']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Judul Buku <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Judul lengkap buku..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Nama Penulis / Pengarang <span class="text-danger">*</span></label>
                            <input type="text" name="author" class="form-control" placeholder="Contoh: Robert C. Martin" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Penerbit <span class="text-danger">*</span></label>
                            <input type="text" name="publisher" class="form-control" placeholder="Contoh: Prentice Hall" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Tahun Terbit <span class="text-danger">*</span></label>
                            <input type="number" name="year" class="form-control" min="1900" max="2099" value="<?= date('Y') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Stok Eksemplar Fisik <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" min="0" value="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">URL Gambar Cover</label>
                            <input type="url" name="cover_url" class="form-control" placeholder="https://...">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Sinopsis / Ringkasan Buku</label>
                            <textarea name="synopsis" class="form-control" rows="3" placeholder="Sinopsis atau pembahasan singkat isi buku..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit_book" class="btn btn-primary-custom">Simpan Buku</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Buku -->
<div class="modal fade" id="modalEditBuku" tabindex="-1" aria-labelledby="modalEditBukuLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php?page=books&action=update">
                <input type="hidden" name="id" id="edit_book_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark" id="modalEditBukuLabel">
                        <i class="fa-solid fa-pen-to-square text-warning me-2"></i>Perbarui Informasi Buku
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Nomor ISBN <span class="text-danger">*</span></label>
                            <input type="text" name="isbn" id="edit_book_isbn" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Kategori Buku <span class="text-danger">*</span></label>
                            <select name="category_id" id="edit_book_catid" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Judul Buku <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="edit_book_title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Nama Penulis / Pengarang <span class="text-danger">*</span></label>
                            <input type="text" name="author" id="edit_book_author" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Penerbit <span class="text-danger">*</span></label>
                            <input type="text" name="publisher" id="edit_book_publisher" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Tahun Terbit <span class="text-danger">*</span></label>
                            <input type="number" name="year" id="edit_book_year" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Stok Eksemplar Fisik <span class="text-danger">*</span></label>
                            <input type="number" name="stock" id="edit_book_stock" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">URL Gambar Cover</label>
                            <input type="url" name="cover_url" id="edit_book_cover" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Sinopsis / Ringkasan Buku</label>
                            <textarea name="synopsis" id="edit_book_synopsis" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_book" class="btn btn-warning text-dark fw-semibold">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Buku -->
<div class="modal fade" id="modalDetailBuku" tabindex="-1" aria-labelledby="modalDetailBukuLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark" id="modalDetailBukuLabel">
                    <i class="fa-solid fa-circle-info text-info me-2"></i>Detail Koleksi Buku
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-3 mb-3">
                    <img id="detail_book_cover" src="" alt="Cover" class="rounded shadow-sm" style="width: 100px; height: 140px; object-fit: cover;">
                    <div>
                        <h5 class="fw-bold text-dark mb-1" id="detail_book_title"></h5>
                        <p class="text-muted small mb-1"><i class="fa-solid fa-pen-nib me-1"></i><span id="detail_book_author"></span></p>
                        <p class="text-secondary small mb-2"><i class="fa-solid fa-building me-1"></i><span id="detail_book_publisher"></span> (<span id="detail_book_year"></span>)</p>
                        <span class="badge bg-primary text-white" id="detail_book_category"></span>
                    </div>
                </div>
                <hr>
                <div class="row g-2 small mb-3">
                    <div class="col-6"><strong>ISBN:</strong> <span id="detail_book_isbn" class="font-monospace"></span></div>
                    <div class="col-6"><strong>Lokasi Rak:</strong> <span id="detail_book_shelf"></span></div>
                    <div class="col-6"><strong>Sisa Stok:</strong> <span id="detail_book_stock" class="fw-bold"></span> eksemplar</div>
                </div>
                <div class="bg-light p-3 rounded">
                    <strong class="d-block small text-muted mb-1">Sinopsis:</strong>
                    <p class="small text-secondary mb-0" id="detail_book_synopsis" style="line-height: 1.6;"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal Listener
    const editButtons = document.querySelectorAll('.btn-edit-book');
    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_book_id').value = this.dataset.id;
            document.getElementById('edit_book_isbn').value = this.dataset.isbn;
            document.getElementById('edit_book_title').value = this.dataset.title;
            document.getElementById('edit_book_author').value = this.dataset.author;
            document.getElementById('edit_book_publisher').value = this.dataset.publisher;
            document.getElementById('edit_book_year').value = this.dataset.year;
            document.getElementById('edit_book_catid').value = this.dataset.catid;
            document.getElementById('edit_book_stock').value = this.dataset.stock;
            document.getElementById('edit_book_cover').value = this.dataset.cover;
            document.getElementById('edit_book_synopsis').value = this.dataset.synopsis;
        });
    });

    // Detail Modal Listener
    const detailButtons = document.querySelectorAll('.btn-detail-book');
    detailButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('detail_book_title').innerText = this.dataset.title;
            document.getElementById('detail_book_author').innerText = this.dataset.author;
            document.getElementById('detail_book_publisher').innerText = this.dataset.publisher;
            document.getElementById('detail_book_year').innerText = this.dataset.year;
            document.getElementById('detail_book_category').innerText = this.dataset.category;
            document.getElementById('detail_book_shelf').innerText = this.dataset.shelf;
            document.getElementById('detail_book_isbn').innerText = this.dataset.isbn;
            document.getElementById('detail_book_stock').innerText = this.dataset.stock;
            document.getElementById('detail_book_synopsis').innerText = this.dataset.synopsis;
            
            const coverEl = document.getElementById('detail_book_cover');
            if (this.dataset.cover) {
                coverEl.src = this.dataset.cover;
                coverEl.style.display = 'block';
            } else {
                coverEl.src = 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=100';
            }
        });
    });
});
</script>

