<?php if (!isset($user) || !Auth::isLoggedIn()): ?>
    <!-- Landing Page -->
    <div class="landing-page">
        <div class="hero-section bg-gradient text-white text-center py-5">
            <div class="container">
                <h1 class="display-4 fw-bold">PENDATAAN USULAN BANTUAN DESA</h1>
                <p class="lead">Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa (SI-PUSBAN)</p>
                <p class="text-muted">Tahun Anggaran <?php echo date('Y'); ?></p>
                <div class="mt-4">
                    <a href="/index.php?page=login" class="btn btn-light btn-lg me-2">Login</a>
                    <a href="/index.php?page=register" class="btn btn-outline-light btn-lg">Registrasi</a>
                </div>
            </div>
        </div>

        <div class="info-section py-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Informasi Desa</h3>
                        <ul class="list-unstyled">
                            <li><strong>Nama Desa:</strong> <?php echo Config::get('DESA_NAME', 'Desa Contoh'); ?></li>
                            <li><strong>Kecamatan:</strong> <?php echo Config::get('KECAMATAN', 'Kecamatan Contoh'); ?></li>
                            <li><strong>Kabupaten:</strong> <?php echo Config::get('KABUPATEN', 'Kabupaten Contoh'); ?></li>
                            <li><strong>Provinsi:</strong> <?php echo Config::get('PROVINSI', 'Provinsi Contoh'); ?></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h3>Fitur Sistem</h3>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Registrasi & Verifikasi OTP</li>
                            <li><i class="fas fa-check text-success"></i> Manajemen Usulan Bantuan</li>
                            <li><i class="fas fa-check text-success"></i> Upload Dokumen & Verifikasi</li>
                            <li><i class="fas fa-check text-success"></i> Dashboard Analytics</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
