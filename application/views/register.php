<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-plus"></i> Registrasi Akun Baru</h4>
                </div>
                <div class="card-body">
                    <form id="registerForm" method="POST" action="/api/auth/register">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <small class="text-muted">Minimal 3 karakter, hanya huruf dan angka</small>
                        </div>
                        <div class="mb-3">
                            <label for="no_wa" class="form-label">Nomor WhatsApp</label>
                            <input type="tel" class="form-control" id="no_wa" name="no_wa" placeholder="08xxxxxxxxxx" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">Minimal 8 karakter</small>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                        </div>
                        <div class="mb-3">
                            <label for="captcha" class="form-label">Captcha (6 digit)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="captcha" name="captcha" placeholder="Masukkan 6 digit captcha" required>
                                <img id="captchaImage" src="/api/auth/captcha-image" alt="Captcha" style="max-width: 150px; cursor: pointer;" onclick="refreshCaptcha()">
                            </div>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-user-plus"></i> Registrasi</button>
                    </form>
                    <hr>
                    <p class="text-center">Sudah punya akun? <a href="/index.php?page=login">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshCaptcha() {
    document.getElementById('captchaImage').src = '/api/auth/captcha-image?' + new Date().getTime();
}

document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    if (password !== passwordConfirm) {
        alert('Password tidak sesuai');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('/api/auth/register', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Registrasi berhasil! Silakan verifikasi OTP yang dikirim ke WhatsApp');
            window.location.href = '/index.php?page=verify_otp&no_wa=' + encodeURIComponent(document.getElementById('no_wa').value);
        } else {
            alert('Error: ' + data.message);
            refreshCaptcha();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
});
</script>
