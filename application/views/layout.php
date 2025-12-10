<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'SI-PUSBAN'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <?php if (isset($user) && Auth::isLoggedIn()): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/index.php">
                <i class="fas fa-file-contract"></i> SI-PUSBAN
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($user['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="/index.php?page=dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/index.php?page=usulan">Usulan</a></li>
                    <li class="nav-item"><a class="nav-link" href="/index.php?page=users">Kelola Pengguna</a></li>
                    <li class="nav-item"><a class="nav-link" href="/index.php?page=settings">Pengaturan</a></li>
                    <?php elseif ($user['role'] === 'operator'): ?>
                    <li class="nav-item"><a class="nav-link" href="/index.php?page=dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/index.php?page=usulan">Kelola Usulan</a></li>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/index.php?page=dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/index.php?page=form_usulan">Buat Usulan</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['nama_lengkap']); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="/index.php?page=profile">Profil Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/application/controllers/AuthController.php?action=logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="main-content">
        <?php if (isset($content)): ?>
            <?php echo $content; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer bg-light text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 SI-PUSBAN. Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa</p>
            <small class="text-muted">Developed with <i class="fas fa-heart text-danger"></i> for Indonesia</small>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/public/assets/js/main.js"></script>
</body>
</html>
