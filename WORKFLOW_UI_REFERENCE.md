# 🎨 Workflow UI - Visual Reference Guide

## 📊 Component Library

### 1. Statistics Cards

#### Styling Classes
```html
<!-- Card dengan border kiri berdasarkan status -->
<div class="stat-card total">        <!-- Biru -->
<div class="stat-card pending">       <!-- Kuning -->
<div class="stat-card approved">      <!-- Hijau -->
<div class="stat-card review">        <!-- Cyan -->
<div class="stat-card rejected">      <!-- Merah -->
```

#### Struktur
```html
<div class="stat-card [status]">
    <i class="fas fa-[icon] stat-card-icon"></i>
    <div class="stat-card-content">
        <div class="stat-card-label">Label</div>
        <div class="stat-card-value">123</div>
        <div class="stat-card-trend">Trend info</div>
    </div>
</div>
```

#### Icons Reference
```
Total Usulan:           fa-file-alt
Menunggu Verifikasi:    fa-hourglass-start
Sedang Ditinjau:        fa-eye
Disetujui:              fa-check-circle
Ditolak:                fa-times-circle
```

---

### 2. Status Badges

#### Type dan Warna
```html
<span class="status-badge menunggu">Menunggu</span>     <!-- Warning/Kuning -->
<span class="status-badge ditinjau">Ditinjau</span>     <!-- Info/Cyan -->
<span class="status-badge disetujui">Disetujui</span>   <!-- Success/Hijau -->
<span class="status-badge ditolak">Ditolak</span>       <!-- Danger/Merah -->
```

#### Ukuran
```css
padding: 4px 12px;          /* Kompak di cards -->
padding: 10px 20px;         /* Besar di header -->
font-size: 0.75rem;         /* Kecil -->
font-size: 0.9rem;          /* Normal -->
```

---

### 3. Workflow Steps

#### Struktur HTML
```html
<div class="workflow-step [status]">
    <div class="workflow-step-circle">
        <i class="fas fa-[icon]"></i>
    </div>
    <div class="workflow-step-label">Label</div>
</div>
```

#### Status Classes
```
completed    → Hijau, checked icon
active       → Biru, animated pulse
pending      → Kuning, waiting icon
```

#### 5 Tahapan Standar
```
1. Pengajuan         (fa-pen)
2. Verifikasi Awal   (fa-clipboard-check)
3. Tinjauan          (fa-magnifying-glass)
4. Persetujuan       (fa-stamp)
5. Realisasi         (fa-hand-holding-heart)
```

---

### 4. Timeline (Vertical)

#### Struktur
```html
<div class="timeline-stage [status]">
    <div class="timeline-marker">
        <i class="fas fa-[icon]"></i>
    </div>
    <div class="timeline-content">
        <div class="timeline-title">Judul</div>
        <div class="timeline-date">Tanggal</div>
        <div class="timeline-description">Deskripsi</div>
    </div>
</div>
```

#### Status Classes
```
completed   → Marker hijau, border content hijau
active      → Marker biru, pulse animation
pending     → Marker kuning, border content kuning
```

---

### 5. Info Cards / Grid

#### Grid Layout
```html
<div class="info-grid">
    <div class="info-item">...</div>
    <div class="info-item">...</div>
    ...
</div>
```

#### Responsive
```css
grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
/* Desktop: 4 kolom -->
/* Tablet:  2 kolom -->
/* Mobile:  1 kolom -->
```

#### Item Structure
```html
<div class="info-item">
    <div class="info-label">Label Kecil</div>
    <div class="info-value">Nilai Besar</div>
</div>
```

---

### 6. Progress Bars

#### Struktur
```html
<div class="progress-item">
    <span class="progress-label">Label</span>
    <div class="progress-bar-wrapper">
        <div class="progress-bar">
            <div class="progress-bar-fill [type]" 
                 style="width: 35%"></div>
        </div>
    </div>
    <span class="progress-value">35%</span>
</div>
```

#### Type
```
success     → Hijau
warning     → Kuning
info        → Cyan
danger      → Merah
```

---

### 7. Workflow Cards (Grid View)

#### Struktur
```html
<div class="workflow-card [status]">
    <div class="workflow-card-header">
        <span class="workflow-card-id">#ID</span>
        <span class="workflow-card-status">Status</span>
    </div>
    <div class="workflow-card-title">Judul</div>
    <div class="workflow-card-meta">
        <div class="workflow-card-meta-item">
            <i class="fas fa-[icon]"></i>
            <span>Data</span>
        </div>
    </div>
</div>
```

#### Status Classes
```
menunggu    → Gradient kuning
ditinjau    → Gradient biru
disetujui   → Gradient hijau
ditolak     → Gradient merah
```

---

## 🎨 Color Palette

### Primary Colors
```
Primary (Biru)      #2563eb     Used for: Main actions, active states
Success (Hijau)     #059669     Used for: Completed, approved
Warning (Kuning)    #d97706     Used for: Pending, attention
Danger (Merah)      #dc2626     Used for: Rejected, errors
Info (Cyan)         #0891b2     Used for: Info, in-progress
```

### Gray Scale
```
Dark       #1f2937     Text utama
Gray-600   #4b5563     Teks sekunder
Gray-400   #9ca3af     Border
Light      #f3f4f6     Background
White      #ffffff     Cards
```

### Gradients
```css
/* Primary to Info -->
background: linear-gradient(135deg, #2563eb, #0891b2);

/* Success to Green -->
background: linear-gradient(90deg, #059669, #10b981);

/* Dark Navbar -->
background: linear-gradient(135deg, #1f2937, #374151);
```

---

## 🔤 Typography

### Font Family
```css
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
```

### Sizes
```
h1:     2.5rem (40px)    Page title
h2:     2rem (32px)      Section title
h3:     1.5rem (24px)    Card title
body:   1rem (16px)      Normal text
small:  0.85rem (14px)   Meta info
```

### Weights
```
Light:      300
Regular:    400
Medium:     500
Semibold:   600
Bold:       700
```

### Line Height
```
Headings:   1.2
Body:       1.6
```

---

## 📏 Spacing System

### Padding
```
xs:  4px
sm:  8px
md:  16px
lg:  24px
xl:  30px
2xl: 40px
```

### Margins
```
Cards:           30px padding
Sections:        40px margin-bottom
Grid gap:        20px
Item gap:        8px-16px
```

### Dimensions
```
Card border-radius:    12px
Item border-radius:    8px
Button border-radius:  8px
Badge border-radius:   20px (fully rounded)
```

---

## 🎭 Shadows

### Shadow System
```css
sm:     0 2px 8px rgba(0, 0, 0, 0.08)
md:     0 4px 12px rgba(0, 0, 0, 0.10)
lg:     0 12px 24px rgba(0, 0, 0, 0.12)
xl:     0 20px 40px rgba(0, 0, 0, 0.15)
```

### Usage
```
Cards (normal):      shadow-md
Cards (hover):       shadow-lg
Navigation:          shadow-md
Modals:             shadow-xl
Buttons:            shadow-md (on hover)
```

---

## 🔄 Animation System

### Transitions
```css
Fast:      0.2s ease
Normal:    0.3s ease
Slow:      0.5s ease-out
```

### Common Animations
```
Hover effects:      transform scale/translateY
Loading:            pulse animation
Page load:          slideIn animation
Status change:      fadeIn/fadeOut
```

### Example
```css
/* Card hover -->
transform: translateY(-5px);
transition: all 0.3s ease;
box-shadow: 0 12px 24px rgba(0,0,0,0.12);

/* Active state pulse -->
animation: pulse 2s infinite;
```

---

## 📱 Responsive Grid System

### Breakpoints
```css
Desktop:    > 1024px    Full layout
Tablet:     768-1024px  2 column
Mobile:     < 768px     1 column (single stack)
```

### Grid Columns
```css
Desktop:    4 columns (cards)
Tablet:     2 columns
Mobile:     1 column (full width)
```

### Font Scaling
```
Desktop:    16px base
Tablet:     15px base
Mobile:     14px base (for readability)
```

---

## 🎯 Button Styles

### Variants
```html
<button class="btn-custom primary">Action</button>
<button class="btn-custom success">Approve</button>
<button class="btn-custom danger">Reject</button>
<button class="btn-custom secondary">Cancel</button>
```

### States
```
Normal:     bg-color, white text
Hover:      darker bg, shadow
Active:     darkernya lagi
Disabled:   opacity 0.5
```

### Size
```
Compact:    padding: 8px 16px
Normal:     padding: 10px 24px
Large:      padding: 12px 32px
Full width: width: 100%
```

---

## 🎪 Layout Patterns

### Dashboard Pattern
```
┌─────────────────────────────────────┐
│  Header (Navigation bar)            │
├─────────────────────────────────────┤
│  Page Title + Breadcrumb            │
├─────────────────────────────────────┤
│  Stats Grid (4 columns)             │
├─────────────────────────────────────┤
│  Main Content (2+ sections)         │
├─────────────────────────────────────┤
│  Footer (Optional)                  │
└─────────────────────────────────────┘
```

### Card Pattern
```
┌─────────────────────────────┐
│ ■ Icon/Title                │
├─────────────────────────────┤
│ Content area                │
├─────────────────────────────┤
│ Action buttons              │
└─────────────────────────────┘
```

### Form Pattern
```
┌─────────────────────────────┐
│ Field Label (uppercase)     │
│ ┌───────────────────────┐   │
│ │ Input field           │   │
│ └───────────────────────┘   │
│ Helper text                 │
├─────────────────────────────┤
│ [Submit Button]             │
└─────────────────────────────┘
```

---

## 🔌 Component Usage Examples

### Example 1: Stats Section
```html
<div class="stats-grid">
    <div class="stat-card total">
        <i class="fas fa-file-alt stat-card-icon"></i>
        <div class="stat-card-content">
            <div class="stat-card-label">Total Usulan</div>
            <div class="stat-card-value">45</div>
            <div class="stat-card-trend">↑ 15% bulan ini</div>
        </div>
    </div>
    <!-- More cards -->
</div>
```

### Example 2: Workflow Process
```html
<div class="workflow-steps">
    <div class="workflow-step completed">
        <div class="workflow-step-circle"><i class="fas fa-pen"></i></div>
        <div class="workflow-step-label">Pengajuan</div>
    </div>
    <!-- More steps -->
</div>
```

### Example 3: Timeline
```html
<div class="timeline-vertical">
    <div class="timeline-stage completed">
        <div class="timeline-marker"><i class="fas fa-pen"></i></div>
        <div class="timeline-content">
            <div class="timeline-title">Pengajuan Usulan</div>
            <div class="timeline-date">4 Desember 2025</div>
            <div class="timeline-description">Usulan diterima sistem</div>
        </div>
    </div>
    <!-- More stages -->
</div>
```

---

## ✅ Quality Checklist

- [ ] Colors match palette
- [ ] Typography consistent
- [ ] Spacing follows grid
- [ ] Icons from Font Awesome
- [ ] Responsive at all breakpoints
- [ ] Hover states working
- [ ] Animations smooth
- [ ] Contrast accessible (WCAG AA)
- [ ] No overlapping elements
- [ ] Performance optimized

---

## 🚀 Quick Copy-Paste Snippets

### Status Badge
```html
<span class="status-badge [menunggu|ditinjau|disetujui|ditolak]">
    <i class="fas fa-[icon]"></i> Text
</span>
```

### Stat Card
```html
<div class="stat-card [total|pending|review|approved|rejected]">
    <i class="fas fa-[icon] stat-card-icon"></i>
    <div class="stat-card-content">
        <div class="stat-card-label">Label</div>
        <div class="stat-card-value">123</div>
    </div>
</div>
```

### Workflow Step
```html
<div class="workflow-step [completed|active|pending]">
    <div class="workflow-step-circle"><i class="fas fa-[icon]"></i></div>
    <div class="workflow-step-label">Label</div>
</div>
```

---

## 📞 Design System Support

- **Font Awesome Icons**: https://fontawesome.com/icons
- **CSS Gradients**: https://cssgradient.io/
- **Color Tools**: https://www.colorhexa.com/
- **Typography**: https://fonts.google.com/

---

**Version**: 1.0  
**Last Updated**: Desember 10, 2025  
**Status**: Complete ✓

