<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-sign-in-alt"></i> Login</h4>
                </div>
                <div class="card-body">
                    <form id="loginForm" method="POST" action="/api/auth/login">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="captcha" class="form-label">Captcha (6 digit)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="captcha" name="captcha" placeholder="Masukkan 6 digit captcha" required>
                                <img id="captchaImage" src="/api/auth/captcha-image" alt="Captcha" style="max-width: 150px; cursor: pointer;" onclick="refreshCaptcha()">
                            </div>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt"></i> Login</button>
                    </form>
                    <hr>
                    <p class="text-center">Belum memiliki akun? <a href="/index.php?page=register">Registrasi di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshCaptcha() {
    document.getElementById('captchaImage').src = '/api/auth/captcha-image?' + new Date().getTime();
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/api/auth/login', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Login berhasil!');
            window.location.href = '/index.php?page=dashboard';
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
