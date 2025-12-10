<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <a href="javascript:history.back()" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Usulan</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>No Usulan:</strong> #<?php echo $usulan['id']; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <span class="badge bg-<?php echo $usulan['status'] === 'Disetujui' ? 'success' : ($usulan['status'] === 'Ditolak' ? 'danger' : ($usulan['status'] === 'Ditinjau' ? 'info' : 'warning')); ?>">
                                <?php echo $usulan['status']; ?>
                            </span>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Nama Lengkap:</strong><br><?php echo htmlspecialchars($usulan['nama_lengkap']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Jenis Kelamin:</strong><br><?php echo $usulan['jenis_kelamin']; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Nomor NIK:</strong><br><?php echo $usulan['nik']; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Nomor KK:</strong><br><?php echo $usulan['no_kk']; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Alamat:</strong><br><?php echo nl2br(htmlspecialchars($usulan['alamat_lengkap'])); ?>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Jenis Bantuan:</strong><br><?php echo htmlspecialchars($usulan['jenis_bantuan']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Tanggal Usulan:</strong><br><?php echo date('d/m/Y H:i', strtotime($usulan['tgl_usulan'])); ?>
                        </div>
                    </div>
                    <?php if (!empty($usulan['deskripsi_bantuan'])): ?>
                    <div class="mb-3">
                        <strong>Keterangan:</strong><br><?php echo nl2br(htmlspecialchars($usulan['deskripsi_bantuan'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Dokumen Pendukung</h5>
                </div>
                <div class="card-body">
                    <?php if ($doc_status['all_uploaded']): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Semua dokumen telah diunggah
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Dokumen yang masih diperlukan:
                        <ul class="mb-0">
                            <?php foreach ($doc_status['missing'] as $doc): ?>
                            <li><?php echo $doc; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Jenis Dokumen</th>
                                <th>Nama File</th>
                                <th>Tanggal Upload</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dokumen)): ?>
                                <?php foreach ($dokumen as $doc): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($doc['tipe_dokumen']); ?></td>
                                    <td><?php echo htmlspecialchars($doc['nama_file_di_cloud']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($doc['uploaded_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($doc['cloud_url']); ?>" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="deleteDoc(<?php echo $doc['id']; ?>)">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada dokumen</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <hr>

                    <h6>Unggah Dokumen</h6>
                    <form id="uploadForm" method="POST" action="/api/usulan/upload-document" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="tipe_dokumen" class="form-label">Jenis Dokumen</label>
                            <select class="form-select" id="tipe_dokumen" name="tipe_dokumen" required>
                                <option value="">Pilih Jenis Dokumen...</option>
                                <option value="KTP">Foto KTP</option>
                                <option value="KK">Foto Kartu Keluarga</option>
                                <option value="RMH_DEPAN">Foto Rumah Tampak Depan</option>
                                <option value="RMH_SAMPING">Foto Rumah Tampak Samping</option>
                                <option value="RMH_DALAM">Foto Rumah Bagian Dalam</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="document" class="form-label">Pilih File</label>
                            <input type="file" class="form-control" id="document" name="document" accept="image/*,.pdf" required>
                            <small class="text-muted">Format: JPG, PNG, GIF, atau PDF. Max 5MB</small>
                        </div>
                        <input type="hidden" name="usulan_id" value="<?php echo $usulan['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Unggah Dokumen
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Catatan Admin</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($usulan['catatan_admin'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($usulan['catatan_admin'])); ?></p>
                    <?php else: ?>
                    <p class="text-muted">Belum ada catatan</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($usulan['status'] === 'Ditolak'): ?>
            <div class="card mt-3 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Alasan Penolakan</h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($usulan['alasan_penolakan'] ?? '-')); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/api/usulan/upload-document', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Dokumen berhasil diunggah!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
});

function deleteDoc(docId) {
    if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) return;
    
    const formData = new FormData();
    formData.append('doc_id', docId);
    formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
    
    fetch('/api/usulan/delete-document', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Dokumen berhasil dihapus!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}
</script>
