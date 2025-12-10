# Panduan Implementasi Workflow UI - SI-PUSBAN

## 🚀 Quick Start (5 Menit)

### 1. Lihat Dashboard Workflow
```bash
# File sudah tersedia di:
/application/views/workflow_dashboard.php

# Buka di browser:
http://localhost:8000/workflow-dashboard
```

### 2. Lihat Detail Usulan dengan Workflow
```bash
# File sudah tersedia di:
/application/views/detail_usulan_workflow.php

# Buka di browser:
http://localhost:8000/usulan/1/workflow
```

### 3. Baca Dokumentasi
```bash
# Dokumentasi lengkap di:
WORKFLOW_DOCUMENTATION.md
```

---

## 📊 Fitur Utama

### ✨ Dashboard Workflow
- **Statistics Cards**: 5 kartu status dengan icon
- **Process Visualization**: Diagram alur 5 tahapan
- **Progress Bars**: Visual progress untuk setiap status
- **Workflow Cards**: Grid view usulan terbaru
- **Activity Timeline**: Riwayat aktivitas real-time

### 📋 Detail Usulan Workflow
- **Header**: Nomor usulan + status badge
- **Vertical Timeline**: Alur perjalanan dengan marker
- **Info Sections**: Data diri, alamat, bantuan, dokumen
- **Document Gallery**: Preview dokumen pendukung
- **Admin Notes**: Catatan verifikasi setiap tahapan
- **Action Buttons**: Download, cetak, kembali

---

## 🔌 Integrasi dengan Aplikasi Existing

### Step 1: Update Router
```php
// Di public/index.php atau router file
$router->get('/workflow-dashboard', 'DashboardController@workflow');
$router->get('/usulan/:id/workflow', 'UsulanController@workflowDetail');
```

### Step 2: Update Controller
```php
// Di application/controllers/DashboardController.php
public function workflow() {
    // Get data dari database
    $usulanModel = new Usulan();
    $proposals = $usulanModel->getAll();
    
    // Hitung statistik
    $stats = [
        'total' => count($proposals),
        'menunggu' => count(array_filter($proposals, fn($p) => $p['status'] === 'menunggu')),
        'ditinjau' => count(array_filter($proposals, fn($p) => $p['status'] === 'ditinjau')),
        'disetujui' => count(array_filter($proposals, fn($p) => $p['status'] === 'disetujui')),
        'ditolak' => count(array_filter($proposals, fn($p) => $p['status'] === 'ditolak'))
    ];
    
    return view('workflow_dashboard', [
        'stats' => $stats,
        'proposals' => $proposals
    ]);
}

public function workflowDetail($id) {
    $usulanModel = new Usulan();
    $proposal = $usulanModel->getById($id);
    $history = $usulanModel->getWorkflowHistory($id);
    
    return view('detail_usulan_workflow', [
        'usulan' => $proposal,
        'history' => $history
    ]);
}
```

### Step 3: Database Migration
```bash
# Jalankan migration untuk tambah workflow fields
php migrate.php
```

```sql
-- Jika manual, jalankan query ini:

-- Tambah field ke table usulan
ALTER TABLE usulan ADD COLUMN workflow_stage VARCHAR(50) DEFAULT 'pengajuan';
ALTER TABLE usulan ADD COLUMN workflow_status VARCHAR(20) DEFAULT 'pending';
ALTER TABLE usulan ADD COLUMN kelayakan_score INT DEFAULT 0;

-- Buat table workflow_history untuk tracking
CREATE TABLE IF NOT EXISTS workflow_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usulan_id INT NOT NULL,
    stage VARCHAR(50),
    status VARCHAR(20),
    notes TEXT,
    admin_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usulan_id) REFERENCES usulan(id)
);
```

---

## 🎯 Tahapan Implementasi

### Phase 1: Setup (Day 1)
- [ ] Copy file workflow views
- [ ] Update database schema
- [ ] Update routing
- [ ] Test akses halaman

### Phase 2: Integration (Day 2)
- [ ] Update controllers
- [ ] Integrate dengan existing data
- [ ] Test data display
- [ ] Fix styling issues

### Phase 3: Enhancement (Day 3)
- [ ] Add real-time updates
- [ ] Implement workflow actions
- [ ] Add notifications
- [ ] Performance optimization

### Phase 4: Deployment (Day 4)
- [ ] Final testing
- [ ] User training
- [ ] Production deployment
- [ ] Monitoring setup

---

## 📝 Workflow Tahapan Detail

```
┌─────────────────────────────────────────────────────────┐
│                  ALUR WORKFLOW USULAN                   │
└─────────────────────────────────────────────────────────┘

1️⃣  PENGAJUAN (Otomatis)
    └─ Usulan diterima dan tercatat
    └─ Status: completed
    └─ Waktu: Seketika

2️⃣  VERIFIKASI AWAL (1-2 hari kerja)
    └─ Check dokumen kelengkapan
    └─ Check keaslian data
    └─ Status: completed/rejected
    └─ Action: Admin

3️⃣  TINJAUAN KELAYAKAN (3-5 hari kerja)
    └─ Verifikasi lapangan
    └─ Penilaian kelayakan (0-100 poin)
    └─ Status: active/completed
    └─ Action: Tim surveyor

4️⃣  PERSETUJUAN AKHIR (1-2 hari kerja)
    └─ Review hasil tinjauan
    └─ Keputusan approve/reject
    └─ Tandatangan pimpinan
    └─ Status: completed
    └─ Action: Kepala Desa

5️⃣  REALISASI (Sesuai jadwal)
    └─ Persiapan penyerahan
    └─ Transfer dana/barang
    └─ Pembuatan BA penyerahan
    └─ Status: completed
    └─ Action: Tim realisasi
```

---

## 🎨 Customization Guide

### Mengubah Warna Status
```css
/* Dalam file workflow view */
:root {
    --primary: #2563eb;      /* Biru */
    --success: #059669;      /* Hijau */
    --warning: #d97706;      /* Kuning */
    --danger: #dc2626;       /* Merah */
    --info: #0891b2;         /* Cyan */
}
```

### Menambah Status Baru
```php
// Di PHP view
<span class="status-badge custom-status">Status Baru</span>

// CSS untuk status baru
.status-badge.custom-status {
    background: #8b5cf6;  /* Purple */
}
```

### Menambah Field Info
```html
<div class="info-item">
    <div class="info-label">Field Baru</div>
    <div class="info-value"><?php echo $data['field']; ?></div>
</div>
```

---

## 🔍 Testing Checklist

### Visual Testing
- [ ] Semua warna tampil dengan benar
- [ ] Icons loading dengan baik
- [ ] Responsive di mobile/tablet
- [ ] Hover effects working
- [ ] Animations smooth

### Functional Testing
- [ ] Data dari database ditampilkan
- [ ] Filter dan sorting bekerja
- [ ] Links navigasi bekerja
- [ ] Buttons responsive
- [ ] Modal/dialog muncul dengan baik

### Performance Testing
- [ ] Page load time < 2 detik
- [ ] No console errors
- [ ] Images optimized
- [ ] CSS/JS minified
- [ ] Database queries optimized

### Cross-Browser Testing
- [ ] Chrome/Chromium
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile browsers

---

## 📱 Responsive Breakpoints

```css
/* Desktop (>1024px) */
- Full layout
- Multiple columns
- All features visible

/* Tablet (768px - 1024px) */
- 2 column layout
- Adjusted grid
- Condensed timeline

/* Mobile (<768px) */
- Single column
- Full width
- Simplified timeline
- Touch-optimized buttons
```

---

## 🚨 Troubleshooting

### Dashboard tidak load
**Problem**: Halaman kosong atau error
**Solution**:
1. Check database connection
2. Verify database tables exist
3. Check error logs
4. Clear browser cache

### Data tidak muncul
**Problem**: Statistics cards kosong
**Solution**:
1. Verify database queries
2. Check data exists in database
3. Inspect browser network tab
4. Check PHP error reporting

### Timeline tidak muncul
**Problem**: Workflow stages tidak terlihat
**Solution**:
1. Check SQL workflow_history table
2. Verify data dalam tabel
3. Check PHP render
4. Inspect HTML elements

### Styling issues
**Problem**: Layout berantakan
**Solution**:
1. Clear browser cache (Ctrl+Shift+Del)
2. Check CSS loaded properly
3. Verify no CSS conflicts
4. Check browser console

---

## 📊 Sample Data untuk Testing

```php
// Test data dapat diinsert ke database
INSERT INTO usulan (id, nama_lengkap, status, nik, no_kk, created_at) VALUES
(1, 'Ahmad Riyadi', 'menunggu', '1234567890123456', '9876543210987654', NOW()),
(2, 'Siti Nurhaliza', 'ditinjau', '2345678901234567', '0987654321098765', NOW()),
(3, 'Muhammad Hasan', 'disetujui', '3456789012345678', '1098765432109876', NOW()),
(4, 'Rina Suryanto', 'ditolak', '4567890123456789', '2109876543210987', NOW());

// Workflow history sample
INSERT INTO workflow_history (usulan_id, stage, status, notes) VALUES
(1, 'pengajuan', 'completed', 'Usulan diterima'),
(1, 'verifikasi', 'completed', 'Dokumen lengkap'),
(1, 'tinjauan', 'active', 'Sedang dalam survei lapangan');
```

---

## 🔐 Security Notes

1. **Input Validation**: Selalu validasi di server-side
2. **Authorization**: Check user role sebelum akses
3. **CSRF Protection**: Gunakan token untuk form
4. **SQL Injection**: Gunakan prepared statements
5. **XSS Prevention**: Escape output HTML

```php
// Contoh secure code
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

$id = (int)$_GET['id'];  // Sanitize input
$data = htmlspecialchars($data);  // Escape output
```

---

## 📞 Support & Resources

- **Documentation**: WORKFLOW_DOCUMENTATION.md
- **Bootstrap**: https://getbootstrap.com/docs/5.3/
- **Font Awesome**: https://fontawesome.com/docs
- **JavaScript**: https://developer.mozilla.org/en-US/docs/Web/JavaScript

---

## ✅ Checklist Sebelum Go Live

- [ ] Semua file sudah ter-copy dengan benar
- [ ] Database migration sudah jalan
- [ ] Routes sudah dikonfigurasi
- [ ] Controllers sudah updated
- [ ] Testing semua fitur
- [ ] Performance check
- [ ] Security audit
- [ ] User documentation ready
- [ ] Team training complete
- [ ] Monitoring setup

---

**Status**: ✅ Ready for Implementation  
**Version**: 1.0  
**Last Updated**: Desember 10, 2025

