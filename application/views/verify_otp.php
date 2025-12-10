<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-shield-alt"></i> Verifikasi OTP</h4>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted mb-4">
                        Kode OTP telah dikirim ke WhatsApp Anda (<?php echo htmlspecialchars($no_wa); ?>)
                    </p>
                    <form id="otpForm" method="POST" action="/api/auth/verify-otp">
                        <div class="mb-3">
                            <label for="otp" class="form-label">Masukkan Kode OTP (6 digit)</label>
                            <input type="text" class="form-control form-control-lg text-center" id="otp" name="otp" placeholder="000000" maxlength="6" required>
                        </div>
                        <input type="hidden" name="no_wa" value="<?php echo htmlspecialchars($no_wa); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo Auth::generateCsrfToken(); ?>">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check"></i> Verifikasi</button>
                    </form>
                    <hr>
                    <p class="text-center text-muted">
                        <small>Tidak menerima OTP? <a href="#" onclick="resendOTP(); return false;">Kirim ulang</a></small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('otpForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/api/auth/verify-otp', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('OTP berhasil diverifikasi! Akun Anda sudah aktif. Silakan login.');
            window.location.href = '/index.php?page=login';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
});

function resendOTP() {
    alert('Fitur resend OTP belum diimplementasikan');
}
</script>
