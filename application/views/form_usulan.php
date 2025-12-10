<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <h2>Form Usulan Bantuan</h2>
            <p class="text-muted">Silakan isi form berikut untuk mengajukan usulan bantuan</p>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <form id="formUsulan" method="POST" action="/api/usulan/create">
                        <h5 class="mb-3">Informasi Pribadi</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nik" class="form-label">Nomor NIK *</label>
                                <input type="text" class="form-control" id="nik" name="nik" placeholder="16 digit NIK" maxlength="16" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="no_kk" class="form-label">Nomor Kartu Keluarga *</label>
                                <input type="text" class="form-control" id="no_kk" name="no_kk" placeholder="16 digit No KK" maxlength="16" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin *</label>
                                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih...</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="jenis_bantuan" class="form-label">Jenis Bantuan *</label>
                                <select class="form-select" id="jenis_bantuan" name="jenis_bantuan" required>
                                    <option value="">Pilih Jenis Bantuan...</option>
                                    <?php foreach ($jenis_bantuan as $key => $value): ?>
                                    <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($value); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="alamat_lengkap" class="form-label">Alamat Lengkap *</label>
                            <textarea class="form-control" id="alamat_lengkap" name="alamat_lengkap" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_bantuan" class="form-label">Deskripsi / Keterangan Tambahan</label>
                            <textarea class="form-control" id="deskripsi_bantuan" name="deskripsi_bantuan" rows="3" placeholder="Jelaskan alasan dan kondisi Anda (opsional)"></textarea>
                        </div>

                        <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> Ajukan Usulan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formUsulan').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/api/usulan/create', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Usulan berhasil dibuat! Silakan upload dokumen yang diperlukan.');
            window.location.href = '/index.php?page=detail_usulan&id=' + data.data.usulan_id;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
});
</script>
