<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card bg-primary text-white mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Usulan</h5>
                    <h2><?php echo $stats['total'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Menunggu Verifikasi</h5>
                    <h2><?php echo $stats['antri'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Disetujui</h5>
                    <h2><?php echo $stats['disetujui'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card bg-info text-white mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Sedang Ditinjau</h5>
                    <h2><?php echo $stats['ditinjau'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Ditolak</h5>
                    <h2><?php echo $stats['ditolak'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-secondary text-white mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Pengguna</h5>
                    <h2><?php echo $user_count ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Usulan Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>Jenis Bantuan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_usulan)): ?>
                                    <?php foreach ($recent_usulan as $usulan): ?>
                                    <tr>
                                        <td><?php echo $usulan['id']; ?></td>
                                        <td><?php echo htmlspecialchars($usulan['nama_lengkap']); ?></td>
                                        <td><?php echo $usulan['nik']; ?></td>
                                        <td><?php echo htmlspecialchars($usulan['jenis_bantuan']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $usulan['status'] === 'Disetujui' ? 'success' : ($usulan['status'] === 'Ditolak' ? 'danger' : 'warning'); ?>">
                                                <?php echo $usulan['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($usulan['tgl_usulan'])); ?></td>
                                        <td>
                                            <a href="/index.php?page=detail_usulan&id=<?php echo $usulan['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Belum ada usulan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
