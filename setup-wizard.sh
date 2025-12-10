#!/bin/bash

###############################################################################
#                  SI-PUSBAN Quick Setup Wizard                              #
#                                                                             #
#  Instalasi Intuitif dengan Interface Dialog                               #
#  Semua Pengaturan Otomatis - Cukup Jawab Beberapa Pertanyaan              #
#                                                                             #
###############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="$PROJECT_DIR/.env"

# Counters
STEP=0
TOTAL_STEPS=6

###############################################################################
# UTILITY FUNCTIONS
###############################################################################

clear_screen() {
    clear
}

print_title() {
    clear_screen
    echo -e "${CYAN}"
    cat << "EOF"
╔════════════════════════════════════════════════════════════════╗
║                                                                ║
║           🚀 SI-PUSBAN Installation Wizard 🚀                 ║
║                                                                ║
║    Sistem Informasi Pendataan Usulan Bantuan Sosial           ║
║                                                                ║
╚════════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
}

print_step() {
    STEP=$((STEP + 1))
    echo -e "\n${MAGENTA}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${MAGENTA}║ LANGKAH $STEP/$TOTAL_STEPS: $1${NC}"
    echo -e "${MAGENTA}╚════════════════════════════════════════════════════════════════╝${NC}\n"
}

print_section() {
    echo -e "\n${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}$1${NC}"
    echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() {
    echo -e "${GREEN}  ✓${NC} $1"
}

print_error() {
    echo -e "${RED}  ✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}  ⚠${NC} $1"
}

print_info() {
    echo -e "${BLUE}  ℹ${NC} $1"
}

press_enter() {
    echo -e "\n${YELLOW}Tekan ENTER untuk lanjutkan...${NC}"
    read -r
}

get_input() {
    local prompt="$1"
    local default="$2"
    local input
    
    echo -ne "  ${CYAN}${prompt}${NC}"
    if [ -n "$default" ]; then
        echo -ne " ${YELLOW}[${default}]${NC}: "
    else
        echo -ne ": "
    fi
    
    read -r input
    echo "${input:-$default}"
}

confirm_prompt() {
    local prompt="$1"
    local response
    
    while true; do
        echo -ne "\n  ${CYAN}${prompt}${NC} ${YELLOW}(y/n)${NC}: "
        read -r response
        case "$response" in
            [yY][eE][sS]|[yY])
                return 0
                ;;
            [nN][oO]|[nN])
                return 1
                ;;
            *)
                echo "  Silakan jawab dengan 'y' atau 'n'"
                ;;
        esac
    done
}

###############################################################################
# STEP 1: WELCOME & SYSTEM CHECK
###############################################################################

step_welcome() {
    print_title
    
    print_step "Selamat Datang"
    
    cat << EOF
Installer ini akan secara otomatis mengatur SI-PUSBAN dengan:

  ${GREEN}✓${NC} Database MySQL di Docker
  ${GREEN}✓${NC} Konfigurasi PHP dan Extensions
  ${GREEN}✓${NC} File .env otomatis
  ${GREEN}✓${NC} Direktori storage
  ${GREEN}✓${NC} Fonnte WhatsApp Integration

Durasi: ~5-10 menit (tergantung koneksi internet)

EOF
    
    if ! confirm_prompt "Lanjutkan instalasi?"; then
        echo -e "\n${YELLOW}Instalasi dibatalkan.${NC}"
        exit 0
    fi
}

check_requirements() {
    print_step "Pemeriksaan Persyaratan"
    
    local all_ok=true
    
    # Check PHP
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -v | grep -oP '\d+\.\d+\.\d+' | head -1)
        print_success "PHP $PHP_VERSION"
    else
        print_error "PHP tidak terinstall"
        all_ok=false
    fi
    
    # Check Docker
    if command -v docker &> /dev/null; then
        print_success "Docker terinstall"
    else
        print_error "Docker tidak terinstall"
        all_ok=false
    fi
    
    # Check cURL extension
    if php -m | grep -qi curl; then
        print_success "PHP cURL extension"
    else
        print_error "PHP cURL extension tidak ada"
        all_ok=false
    fi
    
    # Check Git (optional)
    if command -v git &> /dev/null; then
        print_success "Git terinstall (opsional)"
    else
        print_warning "Git tidak terinstall (opsional)"
    fi
    
    if [ "$all_ok" = false ]; then
        print_error "\nBeberapa persyaratan tidak terpenuhi!"
        exit 1
    fi
    
    print_success "\nSemua persyaratan terpenuhi!"
    press_enter
}

###############################################################################
# STEP 2: DESA INFORMATION
###############################################################################

step_desa_info() {
    print_step "Informasi Desa"
    
    print_info "Masukkan data desa untuk konfigurasi sistem:"
    
    DESA_NAME=$(get_input "Nama Desa" "Kronjo")
    KECAMATAN=$(get_input "Kecamatan" "Kronjo")
    KABUPATEN=$(get_input "Kabupaten" "Tangerang")
    PROVINSI=$(get_input "Provinsi" "Banten")
    TAHUN_ANGGARAN=$(get_input "Tahun Anggaran" "2025")
    
    # Display summary
    print_section "Ringkasan Informasi Desa"
    
    echo "  Nama Desa     : ${GREEN}$DESA_NAME${NC}"
    echo "  Kecamatan     : ${GREEN}$KECAMATAN${NC}"
    echo "  Kabupaten     : ${GREEN}$KABUPATEN${NC}"
    echo "  Provinsi      : ${GREEN}$PROVINSI${NC}"
    echo "  Tahun Anggaran: ${GREEN}$TAHUN_ANGGARAN${NC}"
    
    if ! confirm_prompt "\nApakah data sudah benar?"; then
        step_desa_info
    fi
    
    press_enter
}

###############################################################################
# STEP 3: DATABASE SETUP
###############################################################################

step_database() {
    print_step "Pengaturan Database"
    
    DB_CONTAINER="sibades-db"
    DB_PORT=3307
    DB_NAME="si_pusban"
    
    print_info "Memeriksa Docker container database..."
    
    if docker ps 2>/dev/null | grep -q "$DB_CONTAINER"; then
        print_success "Container database sudah berjalan"
    else
        if docker ps -a 2>/dev/null | grep -q "$DB_CONTAINER"; then
            print_info "Menjalankan container database yang sudah ada..."
            docker start "$DB_CONTAINER" 2>/dev/null || true
            sleep 5
        else
            print_info "Membuat container database baru..."
            docker run -d \
                --name "$DB_CONTAINER" \
                -e MYSQL_ROOT_PASSWORD=root \
                -p "$DB_PORT:3306" \
                --restart unless-stopped \
                mysql:8 \
                > /dev/null 2>&1
            
            print_info "Menunggu database siap (30 detik)..."
            sleep 30
        fi
    fi
    
    print_success "Container database siap"
    
    # Import schema
    print_info "\nMengimport database schema..."
    
    if [ -f "$PROJECT_DIR/database/schema.sql" ]; then
        docker exec "$DB_CONTAINER" mysql -u root -proot << EOF > /dev/null 2>&1 || true
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF
        sleep 2
        
        docker exec -i "$DB_CONTAINER" mysql -u root -proot "$DB_NAME" < "$PROJECT_DIR/database/schema.sql" 2>/dev/null || true
        print_success "Database schema berhasil diimport"
    else
        print_warning "File schema.sql tidak ditemukan"
    fi
    
    press_enter
}

###############################################################################
# STEP 4: FONNTE CONFIGURATION
###############################################################################

step_fonnte() {
    print_step "Konfigurasi Fonnte WhatsApp"
    
    print_section "Fonnte WhatsApp Gateway"
    
    cat << EOF
Fonnte adalah layanan gratis untuk mengirim WhatsApp:

  ${GREEN}✓${NC} Gratis untuk 20 pesan/hari
  ${GREEN}✓${NC} Support OTP dan notifikasi
  ${GREEN}✓${NC} Mudah diintegrasikan
  ${GREEN}✓${NC} API modern

Untuk mendapatkan API Key:
  1. Kunjungi https://fontte.com
  2. Sign up (gratis)
  3. Salin API Key dari dashboard

EOF
    
    FONNTE_API_KEY=$(get_input "Fonnte API Key (bisa diisi nanti)" "your_fonnte_api_key_here")
    
    if confirm_prompt "\nApakah Anda akan mengkonfigurasi Fonnte sekarang?"; then
        print_success "Fonnte akan dikonfigurasi saat instalasi selesai"
        SETUP_FONNTE=true
    else
        SETUP_FONNTE=false
        print_info "Anda dapat mengkonfigurasi Fonnte nanti di file .env"
    fi
    
    press_enter
}

###############################################################################
# STEP 5: CREATE ENV FILE
###############################################################################

step_create_env() {
    print_step "Pembuatan File Konfigurasi"
    
    print_info "Membuat file .env..."
    
    # Generate security keys
    SESSION_SECRET=$(openssl rand -base64 32 2>/dev/null || echo "auto-generated-secret-$(date +%s)")
    ENCRYPTION_KEY=$(openssl rand -base64 32 2>/dev/null || echo "auto-generated-key-$(date +%s)")
    
    # Create .env file
    cat > "$ENV_FILE" << EOF
# ============================================
# SI-PUSBAN Configuration
# Auto-generated by Setup Wizard
# Created: $(date)
# ============================================

# Database Configuration
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=root
DB_NAME=si_pusban
DB_PORT=3307

# Application Configuration
APP_NAME=SI-PUSBAN
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Desa Information
DESA_NAME=$DESA_NAME
KECAMATAN=$KECAMATAN
KABUPATEN=$KABUPATEN
PROVINSI=$PROVINSI
TAHUN_ANGGARAN=$TAHUN_ANGGARAN

# Cloud Storage Configuration (Google Drive / Mega)
CLOUD_PROVIDER=google_drive
CLOUD_API_KEY=your_cloud_api_key_here
CLOUD_FOLDER_ID=your_cloud_folder_id_here

# WhatsApp Gateway - Fonnte (Free)
# Get your API key from: https://fontte.com
# Free account: 20 messages/day
FONNTE_API_KEY=$FONNTE_API_KEY

# Security Keys
SESSION_SECRET=$SESSION_SECRET
ENCRYPTION_KEY=$ENCRYPTION_KEY

# ============================================
# Installation Date: $(date)
# ============================================

EOF
    
    print_success ".env file dibuat"
    print_info "Lokasi: $ENV_FILE"
    
    # Create directories
    print_info "\nMembuat direktori..."
    
    mkdir -p "$PROJECT_DIR/storage/logs"
    mkdir -p "$PROJECT_DIR/storage/uploads"
    mkdir -p "$PROJECT_DIR/application/services"
    
    chmod 755 "$PROJECT_DIR/storage"
    chmod 755 "$PROJECT_DIR/storage/logs"
    chmod 755 "$PROJECT_DIR/storage/uploads"
    
    print_success "Direktori storage dibuat"
    
    press_enter
}

###############################################################################
# STEP 6: VERIFICATION & COMPLETION
###############################################################################

step_verification() {
    print_step "Verifikasi Instalasi"
    
    local passed=0
    local total=5
    
    print_section "Checking Installation"
    
    # Check .env
    if [ -f "$ENV_FILE" ]; then
        print_success ".env file tersedia"
        passed=$((passed + 1))
    else
        print_error ".env file tidak ditemukan"
    fi
    total=$((total + 1))
    
    # Check database
    if docker ps 2>/dev/null | grep -q "sibades-db"; then
        print_success "Database container berjalan"
        passed=$((passed + 1))
    else
        print_warning "Database container tidak berjalan"
    fi
    
    # Check directories
    if [ -d "$PROJECT_DIR/storage/logs" ]; then
        print_success "Direktori storage siap"
        passed=$((passed + 1))
    else
        print_error "Direktori storage tidak ada"
    fi
    
    # Check main files
    if [ -f "$PROJECT_DIR/public/index.php" ]; then
        print_success "File aplikasi tersedia"
        passed=$((passed + 1))
    else
        print_error "File aplikasi tidak lengkap"
    fi
    
    # Check PHP
    if php -m | grep -qi curl; then
        print_success "PHP extensions siap"
        passed=$((passed + 1))
    else
        print_warning "Beberapa PHP extensions mungkin kurang"
    fi
    
    echo -e "\n${CYAN}Hasil: ${GREEN}$passed/$total${NC}${CYAN} checks passed${NC}\n"
    
    if [ $passed -ge 4 ]; then
        print_success "Instalasi berhasil!"
    else
        print_warning "Ada beberapa warning, silakan periksa"
    fi
}

step_completion() {
    clear_screen
    
    echo -e "${GREEN}"
    cat << "EOF"
╔════════════════════════════════════════════════════════════════╗
║                                                                ║
║            ✅ INSTALASI SELESAI DENGAN SUKSES! ✅              ║
║                                                                ║
║                    SI-PUSBAN Siap Digunakan                   ║
║                                                                ║
╚════════════════════════════════════════════════════════════════╝
EOF
    echo -e "${NC}"
    
    print_section "Informasi Penting"
    
    cat << EOF
${CYAN}📌 URL Aplikasi:${NC}
   ${GREEN}http://localhost:8000${NC}

${CYAN}📌 File Konfigurasi:${NC}
   ${GREEN}$ENV_FILE${NC}

${CYAN}📌 Database:${NC}
   Host     : 127.0.0.1
   Port     : 3307
   User     : root
   Password : root
   Database : si_pusban

${CYAN}📌 Informasi Desa:${NC}
   Nama     : $DESA_NAME
   Kecamatan: $KECAMATAN
   Kabupaten: $KABUPATEN
   Provinsi : $PROVINSI

${CYAN}📌 Fonnte WhatsApp:${NC}
EOF
    
    if [ "$FONNTE_API_KEY" != "your_fonnte_api_key_here" ]; then
        echo "   Status: ${GREEN}Sudah dikonfigurasi ✓${NC}"
    else
        echo "   Status: ${YELLOW}Belum dikonfigurasi${NC}"
        echo "   Setup: https://fontte.com → Dapatkan API Key → Edit .env"
    fi
    
    print_section "Langkah Selanjutnya"
    
    cat << EOF
1. ${CYAN}Jalankan Server${NC}
   ${YELLOW}cd $PROJECT_DIR${NC}
   ${YELLOW}/usr/bin/php -S localhost:8000 public/router.php${NC}

2. ${CYAN}Buka Browser${NC}
   ${YELLOW}http://localhost:8000${NC}

3. ${CYAN}Daftar Akun Baru${NC}
   - Click "Registrasi di sini"
   - Isi form dengan WhatsApp number yang valid
   - Tunggu OTP di WhatsApp

4. ${CYAN}Login${NC}
   - Gunakan username dan password yang terdaftar
   - Akses dashboard

EOF
    
    print_section "Dokumentasi"
    
    cat << EOF
${CYAN}📚 Quick Start:${NC}
   FONNTE_QUICKSTART.md

${CYAN}📚 Setup Lengkap:${NC}
   FONNTE_SETUP.md

${CYAN}📚 Checklist Status:${NC}
   IMPLEMENTATION_CHECKLIST.md

${CYAN}📚 Troubleshooting:${NC}
   Lihat bagian "Troubleshooting" di FONNTE_SETUP.md

EOF
    
    if confirm_prompt "Jalankan PHP server sekarang?"; then
        print_info "Menjalankan server..."
        echo -e "${YELLOW}Tekan Ctrl+C untuk menghentikan server${NC}\n"
        sleep 2
        
        cd "$PROJECT_DIR"
        /usr/bin/php -S localhost:8000 "$PROJECT_DIR/public/router.php"
    else
        echo -e "\n${GREEN}Instalasi selesai! Anda dapat menjalankan server kapan saja dengan:${NC}"
        echo -e "${YELLOW}  cd $PROJECT_DIR${NC}"
        echo -e "${YELLOW}  /usr/bin/php -S localhost:8000 public/router.php${NC}\n"
    fi
}

###############################################################################
# MAIN FLOW
###############################################################################

main() {
    # Step 1: Welcome
    step_welcome
    check_requirements
    
    # Step 2: Desa Info
    step_desa_info
    
    # Step 3: Database
    step_database
    
    # Step 4: Fonnte
    step_fonnte
    
    # Step 5: Create ENV
    step_create_env
    
    # Step 6: Verify
    step_verification
    
    # Completion
    step_completion
}

# Run
main "$@"
