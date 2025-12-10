# Dokumentasi Workflow UI - SI-PUSBAN

## 📋 Daftar Isi
1. [Pengenalan Workflow](#pengenalan-workflow)
2. [Komponen UI](#komponen-ui)
3. [Alur Workflow](#alur-workflow)
4. [File-File Terkait](#file-file-terkait)
5. [Integrasi Backend](#integrasi-backend)
6. [Customization](#customization)

---

## 🎯 Pengenalan Workflow

### Apa itu Workflow Dashboard?

Workflow Dashboard adalah sistem visualisasi modern yang menampilkan perjalanan usulan bantuan sosial dari awal hingga selesai. Sistem ini memberikan:

- **Visual Progress Tracking**: Lihat tahapan usulan secara real-time
- **Status Real-time**: Update status otomatis setiap ada perubahan
- **Detail Komprehensif**: Informasi lengkap tentang setiap tahapan proses
- **User-Friendly Interface**: Desain modern dan responsif

### Tahapan Workflow

```
Pengajuan → Verifikasi Awal → Tinjauan Kelayakan → Persetujuan Akhir → Realisasi
   ✓            ✓                 ✓                   ✓                 ○
```

---

## 🎨 Komponen UI

### 1. **Dashboard Overview**
Halaman utama yang menampilkan:

- **Statistics Cards**: 5 kartu informasi utama
  - Total Usulan (Biru)
  - Menunggu Verifikasi (Kuning)
  - Sedang Ditinjau (Cyan)
  - Disetujui (Hijau)
  - Ditolak (Merah)

- **Workflow Process Visualization**: Diagram alur dengan 5 tahapan
- **Progress Tracking**: Bar progress untuk setiap status
- **Recent Proposals**: Grid view usulan terbaru dengan status badge
- **Activity Timeline**: Riwayat aktivitas terbaru

### 2. **Detail Usulan dengan Workflow**
Halaman detail yang menampilkan:

- **Header**: Nomor usulan dan status badge
- **Vertical Timeline**: Alur perjalanan usulan dengan marker
- **Info Sections**: Identitas, alamat, bantuan, dokumen
- **Supporting Documents**: Galeri dokumen pendukung
- **Notes**: Catatan verifikasi admin
- **Action Buttons**: Download, cetak, kembali

### 3. **Status Badges**

```html
<span class="status-badge menunggu">Menunggu</span>
<span class="status-badge ditinjau">Ditinjau</span>
<span class="status-badge disetujui">Disetujui</span>
<span class="status-badge ditolak">Ditolak</span>
```

### 4. **Timeline Markers**

| Status | Icon | Warna | Deskripsi |
|--------|------|-------|-----------|
| Completed | ✓ | Hijau | Tahapan selesai |
| Active | ● | Biru | Tahapan sedang berlangsung |
| Pending | ⌛ | Kuning | Menunggu proses |

---

## 📊 Alur Workflow

### Tahap 1: Pengajuan
- **Durasi**: Otomatis
- **Deskripsi**: Usulan diterima dan tercatat
- **Status**: Completed
- **Action**: Sistem mencatat data pengajuan

### Tahap 2: Verifikasi Awal
- **Durasi**: 1-2 hari kerja
- **Deskripsi**: Kelengkapan dokumen diperiksa
- **Status**: Completed
- **Check**: NIK, KK, dokumen pendukung
- **Output**: Approved/Rejected

### Tahap 3: Tinjauan Kelayakan
- **Durasi**: 3-5 hari kerja
- **Deskripsi**: Verifikasi lapangan dan penilaian kelayakan
- **Status**: Active/Pending
- **Check**: Kondisi rumah, ekonomi keluarga, poin kelayakan
- **Output**: Scoring 0-100

### Tahap 4: Persetujuan Akhir
- **Durasi**: 1-2 hari kerja
- **Deskripsi**: Persetujuan pimpinan
- **Status**: Completed/Pending
- **Decision**: Approve/Reject with reason
- **Output**: SK Persetujuan

### Tahap 5: Realisasi
- **Durasi**: Sesuai jadwal
- **Deskripsi**: Penyerahan bantuan
- **Status**: Active/Completed/Pending
- **Action**: Transfer dana atau penyerahan barang
- **Output**: Berita acara penyerahan

---

## 📁 File-File Terkait

### File Utama

```
application/views/
├── workflow_dashboard.php          # Dashboard workflow utama
├── detail_usulan_workflow.php      # Detail usulan dengan timeline
├── dashboard_admin.php             # Admin dashboard (existing)
└── dashboard_user.php              # User dashboard (existing)

public/assets/css/
└── workflow-styles.css             # Styling khusus workflow (inline di file)

public/assets/js/
└── workflow.js                     # JavaScript functionality (inline di file)

database/
└── schema.sql                      # Database schema (sudah ada)
```

### Integrasi dengan File Existing

File-file baru terintegrasi dengan struktur existing:

```php
// Di DashboardController.php
public function workflow() {
    // Fetch data dari database
    $proposals = $this->UsulanModel->getAll();
    $stats = [
        'total' => count($proposals),
        'menunggu' => count(array_filter($proposals, fn($p) => $p['status'] === 'menunggu')),
        'ditinjau' => count(array_filter($proposals, fn($p) => $p['status'] === 'ditinjau')),
        'disetujui' => count(array_filter($proposals, fn($p) => $p['status'] === 'disetujui')),
        'ditolak' => count(array_filter($proposals, fn($p) => $p['status'] === 'ditolak'))
    ];
    
    return view('workflow_dashboard', ['stats' => $stats, 'proposals' => $proposals]);
}
```

---

## 🔌 Integrasi Backend

### Database Schema Requirement

```sql
-- Table untuk tracking workflow
CREATE TABLE workflow_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usulan_id INT NOT NULL,
    stage VARCHAR(50),           -- 'pengajuan', 'verifikasi', 'tinjauan', 'persetujuan', 'realisasi'
    status VARCHAR(20),          -- 'completed', 'active', 'pending'
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    admin_id INT,
    FOREIGN KEY (usulan_id) REFERENCES usulan(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Modify usulan table
ALTER TABLE usulan ADD COLUMN workflow_stage VARCHAR(50) DEFAULT 'pengajuan';
ALTER TABLE usulan ADD COLUMN workflow_status VARCHAR(20) DEFAULT 'pending';
ALTER TABLE usulan ADD COLUMN kelayakan_score INT DEFAULT 0;
```

### API Endpoints untuk Workflow

```php
// GET /api/workflow/dashboard
// Response: statistics dan proposal list

// GET /api/workflow/detail/{usulan_id}
// Response: detail dengan workflow history

// POST /api/workflow/update/{usulan_id}
// Payload: { stage: 'tinjauan', status: 'completed', notes: '...' }

// GET /api/workflow/history/{usulan_id}
// Response: timeline events
```

### PHP Model Integration

```php
class UsulanModel extends Model {
    public function getWorkflowHistory($usulan_id) {
        return $this->db->query(
            "SELECT * FROM workflow_history WHERE usulan_id = ? ORDER BY timestamp DESC",
            [$usulan_id]
        );
    }
    
    public function updateWorkflowStage($usulan_id, $stage, $notes = '') {
        // Insert to history
        $this->db->insert('workflow_history', [
            'usulan_id' => $usulan_id,
            'stage' => $stage,
            'status' => 'active',
            'notes' => $notes,
            'admin_id' => $_SESSION['user_id']
        ]);
        
        // Update usulan
        $this->db->update('usulan', 
            ['workflow_stage' => $stage], 
            "id = {$usulan_id}"
        );
    }
}
```

---

## 🎨 Customization

### Mengubah Warna Workflow

Edit CSS variables di file:

```css
:root {
    --primary: #2563eb;      /* Biru - primary actions */
    --success: #059669;      /* Hijau - completed */
    --warning: #d97706;      /* Kuning - pending */
    --danger: #dc2626;       /* Merah - rejected */
    --info: #0891b2;         /* Cyan - in review */
}
```

### Menambah Tahapan Workflow

Edit HTML dan JavaScript:

```html
<div class="workflow-step pending">
    <div class="workflow-step-circle">
        <i class="fas fa-check-double"></i>
    </div>
    <div class="workflow-step-label">Tahapan Baru</div>
</div>
```

### Mengubah Status Badge

```html
<!-- Tambah status baru -->
<span class="status-badge custom-status">Custom Status</span>

<!-- CSS untuk status baru -->
<style>
    .status-badge.custom-status {
        background: #8b5cf6;  /* Purple */
    }
</style>
```

### Menambah Info Field

Edit section info-grid:

```html
<div class="info-item">
    <div class="info-label">Field Baru</div>
    <div class="info-value"><?php echo $data['field_baru']; ?></div>
</div>
```

---

## 📱 Responsive Design

Sistem workflow fully responsive untuk:
- **Desktop**: Full layout dengan semua komponen
- **Tablet**: Grid adjusted, timeline condensed
- **Mobile**: Single column, simplified timeline

### Breakpoints

```css
/* Tablet */
@media (max-width: 1024px) {
    /* Komponen teradaptasi */
}

/* Mobile */
@media (max-width: 768px) {
    /* Full responsive redesign */
}
```

---

## 🔐 Security Considerations

1. **Input Validation**: Semua input divalidasi server-side
2. **CSRF Protection**: Token divalidasi untuk semua POST requests
3. **Authorization**: Check role user sebelum akses data
4. **Data Privacy**: Personal data tidak ditampilkan pada list

```php
// Contoh authorization check
if ($_SESSION['role'] !== 'admin' && $_SESSION['user_id'] !== $proposal['user_id']) {
    http_response_code(403);
    exit('Unauthorized');
}
```

---

## 🚀 Deployment Checklist

- [ ] Database migration sudah dijalankan
- [ ] Workflow tables sudah dibuat
- [ ] DashboardController sudah updated
- [ ] Routes sudah dikonfigurasi
- [ ] Assets CSS/JS sudah di-load
- [ ] Testing di berbagai browser
- [ ] Mobile testing selesai
- [ ] Production server ready

---

## 📞 Support & Troubleshooting

### Timeline tidak muncul
- Check browser console untuk error
- Verify PHP error reporting aktif
- Ensure database query working

### Status tidak update
- Check database workflow_history table
- Verify API endpoint returning correct data
- Check JavaScript fetch working

### Styling tidak terlihat
- Ensure CSS inline atau file CSS loaded
- Check browser cache (Ctrl+Shift+Del)
- Verify color variables set correctly

---

## 📚 Referensi

- Bootstrap 5.3: https://getbootstrap.com/docs/5.3/
- Font Awesome 6.4: https://fontawesome.com/docs
- Responsive Web Design: https://www.w3schools.com/css/css_rwd_intro.asp

---

**Versi**: 1.0  
**Last Updated**: Desember 10, 2025  
**Status**: Production Ready ✓

