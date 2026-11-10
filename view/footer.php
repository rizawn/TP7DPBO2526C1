    </main>

    <!-- Footer Modular -->
    <footer>
        <div class="container-fluid px-lg-5">
            <div class="row align-items-center gy-2">
                <div class="col-md-6 text-center text-md-start">
                    <span class="fw-semibold text-dark">PustakaVerse</span> &copy; 2026 &bull; Tugas Praktikum 7 DPBO (OOP PHP GUI).
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <span>Dikembangkan oleh: <strong>Riza Wahyu Nugraha</strong> (NIM 2511421) &bull; Ilmu Komputer UPI</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>

