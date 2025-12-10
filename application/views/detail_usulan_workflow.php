<?php
// Detail Usulan dengan Workflow Visualization
// Menampilkan detail usulan dengan tracking workflow yang interaktif
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Usulan - SI-PUSBAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --info: #0891b2;
            --dark: #1f2937;
            --light: #f3f4f6;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--dark) 0%, #374151 100%);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }

        .content-wrapper {
            padding: 40px 20px;
        }

        /* ========== HEADER ========== */
        .detail-header {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .detail-header-left h1 {
            color: var(--dark);
            font-weight: 700;
            margin-bottom: 5px;
        }

        .detail-header-left p {
            color: #6b7280;
        }

        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .status-badge.menunggu {
            background: var(--warning);
        }

        .status-badge.ditinjau {
            background: var(--info);
        }

        .status-badge.disetujui {
            background: var(--success);
        }

        .status-badge.ditolak {
            background: var(--danger);
        }

        /* ========== WORKFLOW TIMELINE ========== */
        .workflow-timeline {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .timeline-vertical {
            position: relative;
            padding: 30px 0;
        }

        .timeline-vertical::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, var(--primary), var(--success));
        }

        .timeline-stage {
            position: relative;
            margin-bottom: 40px;
            padding-left: 100px;
        }

        .timeline-marker {
            position: absolute;
            left: 0;
            top: 0;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .timeline-stage.completed .timeline-marker {
            background: var(--success);
            border-color: var(--success);
            color: white;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        .timeline-stage.active .timeline-marker {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transform: scale(1.15);
            animation: pulse 2s infinite;
        }

        .timeline-stage.pending .timeline-marker {
            background: #fef3c7;
            border-color: var(--warning);
            color: var(--warning);
        }

        .timeline-content {
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #e5e7eb;
        }

        .timeline-stage.completed .timeline-content {
            border-left-color: var(--success);
        }

        .timeline-stage.active .timeline-content {
            border-left-color: var(--primary);
        }

        .timeline-stage.pending .timeline-content {
            border-left-color: var(--warning);
        }

        .timeline-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .timeline-date {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .timeline-description {
            font-size: 0.95rem;
            color: #6b7280;
        }

        /* ========== INFO CARDS ========== */
        .info-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .section-title i {
            margin-right: 12px;
            color: var(--primary);
            font-size: 1.2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 3px solid var(--primary);
        }

        .info-label {
            color: #6b7280;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .info-value {
            color: var(--dark);
            font-weight: 600;
            font-size: 1rem;
        }

        /* ========== DOCUMENT SECTION ========== */
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .document-card {
            background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.2);
            border-color: var(--primary);
        }

        .document-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .document-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .document-size {
            font-size: 0.85rem;
            color: #6b7280;
        }

        /* ========== ACTION BUTTONS ========== */
        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .btn-custom {
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-custom i {
            font-size: 0.9rem;
        }

        .btn-custom.primary {
            background: var(--primary);
            color: white;
        }

        .btn-custom.primary:hover {
            background: #1d4ed8;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-custom.success {
            background: var(--success);
            color: white;
        }

        .btn-custom.success:hover {
            background: #047857;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        .btn-custom.danger {
            background: var(--danger);
            color: white;
        }

        .btn-custom.danger:hover {
            background: #b91c1c;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .btn-custom.secondary {
            background: #6b7280;
            color: white;
        }

        .btn-custom.secondary:hover {
            background: #4b5563;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .detail-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .status-badge {
                margin-top: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .documents-grid {
                grid-template-columns: 1fr;
            }

            .timeline-stage {
                padding-left: 80px;
            }

            .timeline-marker {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-custom {
                width: 100%;
                justify-content: center;
            }
        }

        /* ========== ANIMATIONS ========== */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .info-section, .workflow-timeline {
            animation: slideIn 0.5s ease-out;
        }

        /* ========== MODAL ========== */
        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
            color: white;
            border: none;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-lg">
            <a class="navbar-brand" href="/">
                <i class="fas fa-tasks"></i>
                SI-PUSBAN
            </a>
        </div>
    </nav>

    <!-- Content -->
    <div class="content-wrapper">
        <div class="container-lg">
            <!-- Header -->
            <div class="detail-header">
                <div class="detail-header-left">
                    <h1><i class="fas fa-file-alt"></i> Usulan #SIBADES-001</h1>
                    <p>Usulan Bantuan Sosial - Keluarga Bapak Ahmad Riyadi</p>
                </div>
                <div>
                    <span class="status-badge disetujui">
                        <i class="fas fa-check-circle"></i> Disetujui
                    </span>
                </div>
            </div>

            <!-- Workflow Timeline -->
            <div class="workflow-timeline">
                <h5 class="mb-4"><i class="fas fa-diagram-project"></i> Alur Perjalanan Usulan</h5>
                <div class="timeline-vertical">
                    <!-- Stage 1: Pengajuan -->
                    <div class="timeline-stage completed">
                        <div class="timeline-marker">
                            <i class="fas fa-pen"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Pengajuan Usulan</div>
                            <div class="timeline-date">4 Desember 2025 - 08:30 WIB</div>
                            <div class="timeline-description">Usulan telah diterima dan tercatat dalam sistem</div>
                        </div>
                    </div>

                    <!-- Stage 2: Verifikasi Awal -->
                    <div class="timeline-stage completed">
                        <div class="timeline-marker">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Verifikasi Awal</div>
                            <div class="timeline-date">5 Desember 2025 - 10:15 WIB</div>
                            <div class="timeline-description">Dokumen dan data lengkap telah diverifikasi</div>
                        </div>
                    </div>

                    <!-- Stage 3: Tinjauan Kelayakan -->
                    <div class="timeline-stage completed">
                        <div class="timeline-marker">
                            <i class="fas fa-magnifying-glass"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Tinjauan Kelayakan</div>
                            <div class="timeline-date">7 Desember 2025 - 14:45 WIB</div>
                            <div class="timeline-description">Usulan telah melewati tinjauan kelayakan dengan hasil positif</div>
                        </div>
                    </div>

                    <!-- Stage 4: Persetujuan Akhir -->
                    <div class="timeline-stage completed">
                        <div class="timeline-marker">
                            <i class="fas fa-stamp"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Persetujuan Akhir</div>
                            <div class="timeline-date">8 Desember 2025 - 11:20 WIB</div>
                            <div class="timeline-description">Usulan telah mendapat persetujuan final dari pimpinan</div>
                        </div>
                    </div>

                    <!-- Stage 5: Realisasi -->
                    <div class="timeline-stage active">
                        <div class="timeline-marker">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">Realisasi Bantuan</div>
                            <div class="timeline-date">Dalam Proses</div>
                            <div class="timeline-description">Bantuan sedang dalam tahap persiapan untuk direalisasikan</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Identitas -->
            <div class="info-section">
                <div class="section-title">
                    <i class="fas fa-user"></i>
                    Informasi Identitas Penerima
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-value">Ahmad Riyadi Kusmanto</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nomor Identitas (NIK)</div>
                        <div class="info-value">1234567890123456</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nomor Kartu Keluarga</div>
                        <div class="info-value">9876543210987654</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Jenis Kelamin</div>
                        <div class="info-value">Laki-laki</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tempat, Tanggal Lahir</div>
                        <div class="info-value">Jakarta, 15 Maret 1980</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nomor Telepon</div>
                        <div class="info-value">+62 812-3456-7890</div>
                    </div>
                </div>
            </div>

            <!-- Info Alamat -->
            <div class="info-section">
                <div class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Informasi Alamat
                </div>
                <div class="info-grid">
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">Alamat Lengkap</div>
                        <div class="info-value">Jl. Merdeka No. 45, RT 03/RW 05, Kelurahan Jambi Lama, Kecamatan Alam Barajo, Kabupaten Jambi, Provinsi Jambi 36122</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kelurahan</div>
                        <div class="info-value">Jambi Lama</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kecamatan</div>
                        <div class="info-value">Alam Barajo</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kabupaten</div>
                        <div class="info-value">Jambi</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Provinsi</div>
                        <div class="info-value">Jambi</div>
                    </div>
                </div>
            </div>

            <!-- Info Bantuan -->
            <div class="info-section">
                <div class="section-title">
                    <i class="fas fa-hand-holding-heart"></i>
                    Informasi Bantuan
                </div>
                <div class="info-grid">
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">Jenis Bantuan</div>
                        <div class="info-value">Bantuan Kesehatan & Gizi</div>
                    </div>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">Alasan Pengajuan</div>
                        <div class="info-value">Keluarga membutuhkan bantuan untuk kesehatan dan gizi anggota keluarga karena terbatasnya penghasilan. Terdapat 2 anak yang masih usia sekolah dan membutuhkan asupan gizi yang cukup untuk tumbuh kembang optimal.</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Estimasi Nominal</div>
                        <div class="info-value">Rp 2.500.000,-</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Prioritas</div>
                        <div class="info-value">Tinggi</div>
                    </div>
                </div>
            </div>

            <!-- Dokumen Pendukung -->
            <div class="info-section">
                <div class="section-title">
                    <i class="fas fa-file-pdf"></i>
                    Dokumen Pendukung
                </div>
                <div class="documents-grid">
                    <div class="document-card">
                        <div class="document-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="document-name">KTP Elektronik</div>
                        <div class="document-size">2.4 MB</div>
                    </div>
                    <div class="document-card">
                        <div class="document-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="document-name">Buku Nikah</div>
                        <div class="document-size">1.8 MB</div>
                    </div>
                    <div class="document-card">
                        <div class="document-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="document-name">Sertifikat Tanah</div>
                        <div class="document-size">3.2 MB</div>
                    </div>
                    <div class="document-card">
                        <div class="document-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="document-name">Foto Rumah</div>
                        <div class="document-size">5.6 MB</div>
                    </div>
                </div>
            </div>

            <!-- Catatan Admin -->
            <div class="info-section">
                <div class="section-title">
                    <i class="fas fa-sticky-note"></i>
                    Catatan & Verifikasi
                </div>
                <div style="background: #f9fafb; border-radius: 8px; padding: 20px; border-left: 4px solid var(--info);">
                    <div style="margin-bottom: 20px;">
                        <div style="color: #6b7280; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">VERIFIKASI AWAL - 5 Desember 2025</div>
                        <div style="color: var(--dark); line-height: 1.6;">Semua dokumen telah diterima dengan lengkap. Keaslian dokumen telah diverifikasi melalui sistem dapil. Data diri penerima bantuan sudah sesuai dengan database kependudukan.</div>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <div style="color: #6b7280; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">TINJAUAN KELAYAKAN - 7 Desember 2025</div>
                        <div style="color: var(--dark); line-height: 1.6;">Hasil tinjauan kelayakan menunjukkan bahwa keluarga memenuhi kriteria penerima bantuan dengan nilai poin 85 dari 100. Kondisi rumah dan keadaan ekonomi keluarga sesuai dengan standar yang ditentukan.</div>
                    </div>
                    <div>
                        <div style="color: #6b7280; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">PERSETUJUAN AKHIR - 8 Desember 2025</div>
                        <div style="color: var(--dark); line-height: 1.6;">Disetujui oleh Kepala Desa untuk mendapatkan bantuan sosial sebesar Rp 2.500.000,- dengan kategori Bantuan Kesehatan & Gizi.</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="info-section">
                <div class="action-buttons">
                    <button class="btn-custom primary">
                        <i class="fas fa-file-download"></i> Unduh Berkas
                    </button>
                    <button class="btn-custom secondary">
                        <i class="fas fa-print"></i> Cetak Usulan
                    </button>
                    <button class="btn-custom secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers for document cards
            document.querySelectorAll('.document-card').forEach(card => {
                card.addEventListener('click', function() {
                    console.log('Download:', this.querySelector('.document-name').textContent);
                });
            });

            // Back button handler
            document.querySelector('[class*="secondary"]').addEventListener('click', function() {
                if (this.textContent.includes('Kembali')) {
                    window.history.back();
                }
            });

            // Print button handler
            document.querySelectorAll('.btn-custom.secondary').forEach(btn => {
                if (btn.textContent.includes('Cetak')) {
                    btn.addEventListener('click', function() {
                        window.print();
                    });
                }
            });

            // Download button handler
            document.querySelectorAll('.btn-custom.primary').forEach(btn => {
                if (btn.textContent.includes('Unduh')) {
                    btn.addEventListener('click', function() {
                        alert('Fitur unduh berkas akan diimplementasikan');
                    });
                }
            });
        });
    </script>
</body>
</html>
