<?php
// Workflow Dashboard - Modern UI untuk SI-PUSBAN
// Menampilkan workflow usulan dalam bentuk visual yang interaktif
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow Dashboard - SI-PUSBAN</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 20px 0;
        }

        .navbar {
            background: linear-gradient(135deg, var(--dark) 0%, #374151 100%);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }

        .navbar-brand i {
            margin-right: 8px;
            color: var(--primary);
        }

        .content-wrapper {
            background: white;
            min-height: calc(100vh - 60px);
            padding: 40px 20px;
        }

        .page-header {
            margin-bottom: 40px;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 20px;
        }

        .page-header h1 {
            color: var(--dark);
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #6b7280;
            font-size: 1.1rem;
        }

        /* ========== STATS CARDS ========== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-left: 4px solid;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        .stat-card.total {
            border-left-color: var(--primary);
        }

        .stat-card.pending {
            border-left-color: var(--warning);
        }

        .stat-card.approved {
            border-left-color: var(--success);
        }

        .stat-card.review {
            border-left-color: var(--info);
        }

        .stat-card.rejected {
            border-left-color: var(--danger);
        }

        .stat-card-icon {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 2.5rem;
            opacity: 0.1;
        }

        .stat-card-content {
            position: relative;
            z-index: 2;
        }

        .stat-card-label {
            color: #6b7280;
            font-size: 0.95rem;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .stat-card-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-card-trend {
            margin-top: 10px;
            font-size: 0.85rem;
            color: var(--success);
        }

        /* ========== WORKFLOW STEPS ========== */
        .workflow-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
        }

        .workflow-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }

        .workflow-title i {
            margin-right: 12px;
            color: var(--primary);
        }

        .workflow-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 40px;
        }

        /* Connecting lines */
        .workflow-steps::before {
            content: '';
            position: absolute;
            top: 30px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }

        .workflow-step {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .workflow-step-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .workflow-step.completed .workflow-step-circle {
            background: var(--success);
            border-color: var(--success);
            color: white;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        .workflow-step.active .workflow-step-circle {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transform: scale(1.1);
        }

        .workflow-step.pending .workflow-step-circle {
            background: #fef3c7;
            border-color: var(--warning);
            color: var(--warning);
        }

        .workflow-step-label {
            text-align: center;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .workflow-step.completed .workflow-step-label {
            color: var(--success);
        }

        .workflow-step.active .workflow-step-label {
            color: var(--primary);
        }

        /* ========== WORKFLOW CARDS ========== */
        .workflow-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .workflow-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .workflow-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--info));
        }

        .workflow-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .workflow-card.menunggu {
            background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%);
        }

        .workflow-card.menunggu::before {
            background: linear-gradient(90deg, var(--warning), #d97706);
        }

        .workflow-card.ditinjau {
            background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%);
        }

        .workflow-card.ditinjau::before {
            background: linear-gradient(90deg, var(--info), #0e7490);
        }

        .workflow-card.disetujui {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        }

        .workflow-card.disetujui::before {
            background: linear-gradient(90deg, var(--success), #059669);
        }

        .workflow-card.ditolak {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        }

        .workflow-card.ditolak::before {
            background: linear-gradient(90deg, var(--danger), #b91c1c);
        }

        .workflow-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .workflow-card-id {
            font-weight: 700;
            font-size: 0.9rem;
            color: rgba(0, 0, 0, 0.6);
        }

        .workflow-card-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
        }

        .workflow-card.menunggu .workflow-card-status {
            background: var(--warning);
        }

        .workflow-card.ditinjau .workflow-card-status {
            background: var(--info);
        }

        .workflow-card.disetujui .workflow-card-status {
            background: var(--success);
        }

        .workflow-card.ditolak .workflow-card-status {
            background: var(--danger);
        }

        .workflow-card-title {
            font-weight: 600;
            color: rgba(0, 0, 0, 0.8);
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .workflow-card-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 0.9rem;
            color: rgba(0, 0, 0, 0.6);
        }

        .workflow-card-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .workflow-card-meta-item i {
            width: 18px;
            text-align: center;
            color: rgba(0, 0, 0, 0.4);
        }

        /* ========== PROGRESS BAR ========== */
        .progress-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
        }

        .progress-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 20px;
        }

        .progress-item {
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .progress-label {
            flex: 0 0 150px;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .progress-bar-wrapper {
            flex: 1;
            position: relative;
        }

        .progress-bar {
            height: 10px;
            background: #e5e7eb;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            transition: width 0.5s ease;
            border-radius: 5px;
        }

        .progress-bar-fill.success {
            background: linear-gradient(90deg, var(--success), #10b981);
        }

        .progress-bar-fill.warning {
            background: linear-gradient(90deg, var(--warning), #f59e0b);
        }

        .progress-bar-fill.info {
            background: linear-gradient(90deg, var(--info), #06b6d4);
        }

        .progress-bar-fill.danger {
            background: linear-gradient(90deg, var(--danger), #ef4444);
        }

        .progress-value {
            flex: 0 0 60px;
            text-align: right;
            font-weight: 700;
            color: var(--dark);
            font-size: 1.1rem;
        }

        /* ========== TIMELINE ========== */
        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, var(--primary), var(--success));
        }

        .timeline-item {
            margin-bottom: 30px;
            padding-left: 60px;
            position: relative;
        }

        .timeline-marker {
            position: absolute;
            left: 0;
            top: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: white;
            border: 3px solid var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 0.8rem;
        }

        .timeline-content {
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            border-left: 3px solid var(--primary);
        }

        .timeline-date {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .timeline-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .timeline-description {
            font-size: 0.9rem;
            color: #6b7280;
        }

        /* ========== BUTTONS ========== */
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

        /* ========== RESPONSIVE ========== */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.8rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .workflow-steps {
                flex-direction: column;
            }

            .workflow-steps::before {
                width: 2px;
                height: auto;
                top: 30px;
                left: 30px;
                right: auto;
                bottom: 0;
            }

            .workflow-step {
                margin-bottom: 30px;
                align-items: flex-start;
                padding-left: 40px;
            }

            .workflow-step-circle {
                width: 50px;
                height: 50px;
            }

            .workflow-cards {
                grid-template-columns: 1fr;
            }

            .workflow-container {
                padding: 20px;
            }
        }

        /* ========== ANIMATIONS ========== */
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

        .stat-card, .workflow-container, .progress-section {
            animation: slideIn 0.5s ease-out;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        .workflow-step.active .workflow-step-circle {
            animation: pulse 2s infinite;
        }

        /* ========== EMPTY STATE ========== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 20px;
        }

        .empty-state p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-lg">
            <a class="navbar-brand" href="/">
                <i class="fas fa-tasks"></i>
                SI-PUSBAN Workflow
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Usulan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Laporan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Pengaturan</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="content-wrapper">
        <div class="container-lg">
            <!-- Header -->
            <div class="page-header">
                <h1><i class="fas fa-chart-line"></i> Dashboard Workflow</h1>
                <p>Pantau alur perjalanan usulan bantuan sosial secara real-time</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card total">
                    <i class="fas fa-file-alt stat-card-icon"></i>
                    <div class="stat-card-content">
                        <div class="stat-card-label">Total Usulan</div>
                        <div class="stat-card-value" id="totalCount">0</div>
                        <div class="stat-card-trend"><i class="fas fa-arrow-up"></i> 12% dari bulan lalu</div>
                    </div>
                </div>

                <div class="stat-card pending">
                    <i class="fas fa-hourglass-start stat-card-icon"></i>
                    <div class="stat-card-content">
                        <div class="stat-card-label">Menunggu Verifikasi</div>
                        <div class="stat-card-value" id="menungguCount">0</div>
                        <div class="stat-card-trend">Membutuhkan aksi segera</div>
                    </div>
                </div>

                <div class="stat-card review">
                    <i class="fas fa-eye stat-card-icon"></i>
                    <div class="stat-card-content">
                        <div class="stat-card-label">Sedang Ditinjau</div>
                        <div class="stat-card-value" id="ditinjauCount">0</div>
                        <div class="stat-card-trend">Dalam proses verifikasi</div>
                    </div>
                </div>

                <div class="stat-card approved">
                    <i class="fas fa-check-circle stat-card-icon"></i>
                    <div class="stat-card-content">
                        <div class="stat-card-label">Disetujui</div>
                        <div class="stat-card-value" id="disetujuiCount">0</div>
                        <div class="stat-card-trend">Siap untuk realisasi</div>
                    </div>
                </div>

                <div class="stat-card rejected">
                    <i class="fas fa-times-circle stat-card-icon"></i>
                    <div class="stat-card-content">
                        <div class="stat-card-label">Ditolak</div>
                        <div class="stat-card-value" id="ditolakCount">0</div>
                        <div class="stat-card-trend">Tidak memenuhi kriteria</div>
                    </div>
                </div>
            </div>

            <!-- Workflow Process Visualization -->
            <div class="workflow-container">
                <div class="workflow-title">
                    <i class="fas fa-diagram-project"></i>
                    Alur Proses Usulan
                </div>

                <div class="workflow-steps">
                    <div class="workflow-step completed">
                        <div class="workflow-step-circle">
                            <i class="fas fa-pen"></i>
                        </div>
                        <div class="workflow-step-label">Pengajuan</div>
                    </div>

                    <div class="workflow-step completed">
                        <div class="workflow-step-circle">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="workflow-step-label">Verifikasi Awal</div>
                    </div>

                    <div class="workflow-step active">
                        <div class="workflow-step-circle">
                            <i class="fas fa-magnifying-glass"></i>
                        </div>
                        <div class="workflow-step-label">Tinjauan Kelayakan</div>
                    </div>

                    <div class="workflow-step pending">
                        <div class="workflow-step-circle">
                            <i class="fas fa-stamp"></i>
                        </div>
                        <div class="workflow-step-label">Persetujuan Akhir</div>
                    </div>

                    <div class="workflow-step pending">
                        <div class="workflow-step-circle">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <div class="workflow-step-label">Realisasi</div>
                    </div>
                </div>
            </div>

            <!-- Progress Tracking -->
            <div class="progress-section">
                <div class="progress-title">
                    <i class="fas fa-chart-bar" style="margin-right: 8px; color: var(--primary);"></i>
                    Status Penyelesaian Usulan
                </div>

                <div class="progress-item">
                    <span class="progress-label">Disetujui</span>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar">
                            <div class="progress-bar-fill success" id="approvedBar" style="width: 35%"></div>
                        </div>
                    </div>
                    <span class="progress-value" id="approvedPercent">35%</span>
                </div>

                <div class="progress-item">
                    <span class="progress-label">Menunggu</span>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar">
                            <div class="progress-bar-fill warning" id="pendingBar" style="width: 25%"></div>
                        </div>
                    </div>
                    <span class="progress-value" id="pendingPercent">25%</span>
                </div>

                <div class="progress-item">
                    <span class="progress-label">Ditinjau</span>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar">
                            <div class="progress-bar-fill info" id="reviewBar" style="width: 30%"></div>
                        </div>
                    </div>
                    <span class="progress-value" id="reviewPercent">30%</span>
                </div>

                <div class="progress-item">
                    <span class="progress-label">Ditolak</span>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar">
                            <div class="progress-bar-fill danger" id="rejectedBar" style="width: 10%"></div>
                        </div>
                    </div>
                    <span class="progress-value" id="rejectedPercent">10%</span>
                </div>
            </div>

            <!-- Recent Proposals Workflow -->
            <div class="workflow-container">
                <div class="workflow-title">
                    <i class="fas fa-clock"></i>
                    Usulan Terbaru
                </div>

                <div class="workflow-cards" id="workflowCards">
                    <!-- Cards will be generated here -->
                </div>
            </div>

            <!-- Timeline -->
            <div class="workflow-container">
                <div class="workflow-title">
                    <i class="fas fa-history"></i>
                    Riwayat Aktivitas Terbaru
                </div>

                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker">1</div>
                        <div class="timeline-content">
                            <div class="timeline-date">Hari ini, 10:30</div>
                            <div class="timeline-title">Usulan SIBADES-001 Disetujui</div>
                            <div class="timeline-description">Usulan bantuan untuk keluarga Bpk. Ahmad telah mendapat persetujuan final dari admin.</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">2</div>
                        <div class="timeline-content">
                            <div class="timeline-date">Kemarin, 14:15</div>
                            <div class="timeline-title">Usulan SIBADES-002 Dalam Tinjauan</div>
                            <div class="timeline-description">Dokumen pendukung telah diterima dan sedang dalam tahap verifikasi kelayakan.</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">3</div>
                        <div class="timeline-content">
                            <div class="timeline-date">2 hari yang lalu, 09:20</div>
                            <div class="timeline-title">Usulan SIBADES-003 Ditolak</div>
                            <div class="timeline-description">Usulan tidak memenuhi kriteria karena penghasilan melebihi batas yang ditentukan.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sample data - Replace with actual data from backend
        const workflowData = [
            { id: 'SIBADES-001', nama: 'Usulan Bantuan Keluarga', user: 'Ahmad Riyadi', status: 'disetujui', date: '10 Des 2025' },
            { id: 'SIBADES-002', nama: 'Usulan Bantuan Kesehatan', user: 'Siti Nurhaliza', status: 'ditinjau', date: '09 Des 2025' },
            { id: 'SIBADES-003', nama: 'Usulan Bantuan Pendidikan', user: 'Muhammad Hasan', status: 'menunggu', date: '08 Des 2025' },
            { id: 'SIBADES-004', nama: 'Usulan Bantuan Modal Usaha', user: 'Rina Suryanto', status: 'ditolak', date: '07 Des 2025' },
            { id: 'SIBADES-005', nama: 'Usulan Bantuan Bencana', user: 'Yusuf Ibrahim', status: 'disetujui', date: '06 Des 2025' },
            { id: 'SIBADES-006', nama: 'Usulan Bantuan Sosial', user: 'Fatima Zahra', status: 'menunggu', date: '05 Des 2025' }
        ];

        // Initialize workflow cards
        function initializeWorkflow() {
            const container = document.getElementById('workflowCards');
            
            workflowData.forEach(data => {
                const statusIcon = {
                    'menunggu': 'fa-hourglass-start',
                    'ditinjau': 'fa-eye',
                    'disetujui': 'fa-check-circle',
                    'ditolak': 'fa-times-circle'
                }[data.status];

                const card = document.createElement('div');
                card.className = `workflow-card ${data.status}`;
                card.innerHTML = `
                    <div class="workflow-card-header">
                        <span class="workflow-card-id">${data.id}</span>
                        <span class="workflow-card-status">
                            <i class="fas ${statusIcon}"></i> ${capitalize(data.status)}
                        </span>
                    </div>
                    <div class="workflow-card-title">${data.nama}</div>
                    <div class="workflow-card-meta">
                        <div class="workflow-card-meta-item">
                            <i class="fas fa-user"></i>
                            <span>${data.user}</span>
                        </div>
                        <div class="workflow-card-meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>${data.date}</span>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });

            // Update statistics
            updateStatistics();
        }

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function updateStatistics() {
            const total = workflowData.length;
            const menunggu = workflowData.filter(d => d.status === 'menunggu').length;
            const ditinjau = workflowData.filter(d => d.status === 'ditinjau').length;
            const disetujui = workflowData.filter(d => d.status === 'disetujui').length;
            const ditolak = workflowData.filter(d => d.status === 'ditolak').length;

            document.getElementById('totalCount').textContent = total;
            document.getElementById('menungguCount').textContent = menunggu;
            document.getElementById('ditinjauCount').textContent = ditinjau;
            document.getElementById('disetujuiCount').textContent = disetujui;
            document.getElementById('ditolakCount').textContent = ditolak;

            // Update progress bars
            const approvedPercent = Math.round((disetujui / total) * 100);
            const pendingPercent = Math.round((menunggu / total) * 100);
            const reviewPercent = Math.round((ditinjau / total) * 100);
            const rejectedPercent = Math.round((ditolak / total) * 100);

            document.getElementById('approvedBar').style.width = approvedPercent + '%';
            document.getElementById('approvedPercent').textContent = approvedPercent + '%';

            document.getElementById('pendingBar').style.width = pendingPercent + '%';
            document.getElementById('pendingPercent').textContent = pendingPercent + '%';

            document.getElementById('reviewBar').style.width = reviewPercent + '%';
            document.getElementById('reviewPercent').textContent = reviewPercent + '%';

            document.getElementById('rejectedBar').style.width = rejectedPercent + '%';
            document.getElementById('rejectedPercent').textContent = rejectedPercent + '%';
        }

        // Add interactivity
        document.addEventListener('DOMContentLoaded', function() {
            initializeWorkflow();

            // Add click handlers to workflow cards
            document.querySelectorAll('.workflow-card').forEach(card => {
                card.addEventListener('click', function() {
                    const id = this.querySelector('.workflow-card-id').textContent;
                    console.log('Navigating to detail:', id);
                    // Uncomment to enable navigation:
                    // window.location.href = `/detail_usulan?id=${id}`;
                });
            });

            // Smooth scroll for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
        });
    </script>
</body>
</html>
