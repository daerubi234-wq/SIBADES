<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h2>Dashboard Pengguna</h2>
            <p>Selamat datang, <?php echo htmlspecialchars($user['nama_lengkap']); ?>!</p>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Usulan</h5>
                    <h3><?php echo count($usulan); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Menunggu</h5>
                    <h3><?php echo count(array_filter($usulan, fn($u) => $u['status'] === 'Antri')); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Disetujui</h5>
                    <h3><?php echo count(array_filter($usulan, fn($u) => $u['status'] === 'Disetujui')); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Ditolak</h5>
                    <h3><?php echo count(array_filter($usulan, fn($u) => $u['status'] === 'Ditolak')); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Usulan Saya</h5>
                    <a href="/index.php?page=form_usulan" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Buat Usulan Baru
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Jenis Bantuan</th>
                                    <th>Status</th>
                                    <th>Tanggal Usulan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($usulan)): ?>
                                    <?php $no = 1; foreach ($usulan as $item): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $item['nik']; ?></td>
                                        <td><?php echo htmlspecialchars($item['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($item['jenis_bantuan']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $item['status'] === 'Disetujui' ? 'success' : ($item['status'] === 'Ditolak' ? 'danger' : ($item['status'] === 'Ditinjau' ? 'info' : 'warning')); ?>">
                                                <?php echo $item['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($item['tgl_usulan'])); ?></td>
                                        <td>
                                            <a href="/index.php?page=detail_usulan&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Anda belum memiliki usulan</td>
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
