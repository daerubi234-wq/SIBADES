#!/bin/bash

###############################################################################
# SI-PUSBAN Installation Script
# Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
# 
# This script automates the installation and configuration of SI-PUSBAN
# on a Linux server with Apache, PHP, and MySQL/MariaDB.
###############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored messages
print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to get user input
read_input() {
    local prompt="$1"
    local default="$2"
    local input
    
    if [ -n "$default" ]; then
        echo -n -e "${BLUE}$prompt [$default]: ${NC}"
    else
        echo -n -e "${BLUE}$prompt: ${NC}"
    fi
    
    read -r input
    echo "${input:-$default}"
}

###############################################################################
# START INSTALLATION
###############################################################################

clear
print_header "SI-PUSBAN Installation Wizard"
echo -e "Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa"
echo ""
print_info "Versi: 1.0.0"
echo ""

# Step 1: Check dependencies
print_header "Step 1: Checking Dependencies"

DEPS_OK=true

if command_exists php; then
    PHP_VERSION=$(php -r 'echo phpversion();')
    print_success "PHP is installed (v$PHP_VERSION)"
else
    print_error "PHP is not installed. Please install PHP 7.4+ first."
    DEPS_OK=false
fi

if command_exists mysql; then
    print_success "MySQL/MariaDB client is installed"
else
    print_warning "MySQL/MariaDB client is not installed. You may need it for database management."
fi

if command_exists apache2ctl; then
    print_success "Apache is installed"
else
    print_warning "Apache is not installed. You may need to configure your web server manually."
fi

# Check PHP extensions
if php -m | grep -q "pdo_mysql"; then
    print_success "PHP PDO MySQL extension is installed"
else
    print_error "PHP PDO MySQL extension is not installed"
    DEPS_OK=false
fi

if php -m | grep -q "gd"; then
    print_success "PHP GD extension is installed"
else
    print_warning "PHP GD extension is not installed. Image compression will be limited."
fi

if php -m | grep -q "curl"; then
    print_success "PHP cURL extension is installed"
else
    print_warning "PHP cURL extension is not installed. Cloud integration may not work."
fi

echo ""
if [ "$DEPS_OK" = false ]; then
    print_error "Some required dependencies are missing."
    echo "Please install them and run the script again."
    exit 1
fi

# Step 2: Get database configuration
print_header "Step 2: Database Configuration"

DB_HOST=$(read_input "Database Host" "localhost")
DB_PORT=$(read_input "Database Port" "3306")
DB_USER=$(read_input "Database User" "root")
echo -n -e "${BLUE}Database Password: ${NC}"
read -rs DB_PASS
echo ""
DB_NAME=$(read_input "Database Name" "si_pusban")

# Test database connection
print_info "Testing database connection..."
if mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1" >/dev/null 2>&1; then
    print_success "Database connection successful"
else
    print_error "Cannot connect to database. Please check your credentials."
    exit 1
fi

# Step 3: Get desa information
print_header "Step 3: Desa Information"

DESA_NAME=$(read_input "Desa Name" "Desa Contoh")
KECAMATAN=$(read_input "Kecamatan" "Kecamatan Contoh")
KABUPATEN=$(read_input "Kabupaten" "Kabupaten Contoh")
PROVINSI=$(read_input "Provinsi" "Provinsi Contoh")
TAHUN_ANGGARAN=$(read_input "Tahun Anggaran" "$(date +%Y)")

# Step 4: Get cloud storage configuration
print_header "Step 4: Cloud Storage Configuration"

CLOUD_PROVIDER=$(read_input "Cloud Provider (google_drive/mega/none)" "google_drive")

if [ "$CLOUD_PROVIDER" != "none" ]; then
    CLOUD_API_KEY=$(read_input "Cloud API Key" "")
    CLOUD_FOLDER_ID=$(read_input "Cloud Folder ID" "")
else
    CLOUD_API_KEY=""
    CLOUD_FOLDER_ID=""
fi

# Step 5: Get WhatsApp configuration
print_header "Step 5: WhatsApp Gateway Configuration (Optional)"

WHATSAPP_ENABLE=$(read_input "Enable WhatsApp notifications? (yes/no)" "no")

if [ "$WHATSAPP_ENABLE" = "yes" ]; then
    WHATSAPP_API_KEY=$(read_input "WhatsApp API Key" "")
    WHATSAPP_GATEWAY_URL=$(read_input "WhatsApp Gateway URL" "https://api.whatsapp.com")
else
    WHATSAPP_API_KEY=""
    WHATSAPP_GATEWAY_URL=""
fi

# Step 6: Get web server configuration
print_header "Step 6: Web Server Configuration"

WEB_ROOT=$(read_input "Web Root Directory" "/var/www/si-pusban")
WEB_USER=$(read_input "Web Server User" "www-data")
DOMAIN=$(read_input "Domain/URL" "http://localhost")

# Step 7: Create .env file
print_header "Step 7: Creating Environment Configuration"

ENV_FILE=".env"

cat > "$ENV_FILE" << EOF
# Database Configuration
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
DB_USER=$DB_USER
DB_PASS=$DB_PASS
DB_NAME=$DB_NAME

# Application Configuration
APP_NAME=SI-PUSBAN
APP_ENV=production
APP_DEBUG=false
APP_URL=$DOMAIN

# Desa Information
DESA_NAME=$DESA_NAME
KECAMATAN=$KECAMATAN
KABUPATEN=$KABUPATEN
PROVINSI=$PROVINSI
TAHUN_ANGGARAN=$TAHUN_ANGGARAN

# Cloud Storage Configuration
CLOUD_PROVIDER=$CLOUD_PROVIDER
CLOUD_API_KEY=$CLOUD_API_KEY
CLOUD_FOLDER_ID=$CLOUD_FOLDER_ID

# WhatsApp Gateway
WHATSAPP_API_KEY=$WHATSAPP_API_KEY
WHATSAPP_GATEWAY_URL=$WHATSAPP_GATEWAY_URL

# Security
SESSION_SECRET=$(openssl rand -hex 32)
ENCRYPTION_KEY=$(openssl rand -hex 32)
EOF

print_success ".env file created"

# Step 8: Create/Setup directories
print_header "Step 8: Setting Up Directories"

mkdir -p "storage/uploads"
mkdir -p "storage/logs"

print_success "Directories created"

# Step 9: Set file permissions
print_header "Step 9: Setting File Permissions"

if [ -n "$WEB_USER" ]; then
    chmod -R 755 "public"
    chmod -R 775 "storage"
    chmod 644 "$ENV_FILE"
    chown -R "$WEB_USER:$WEB_USER" "storage" 2>/dev/null || print_warning "Cannot change ownership. Run with sudo if needed."
    print_success "File permissions configured"
else
    chmod -R 755 "public"
    chmod -R 775 "storage"
    chmod 644 "$ENV_FILE"
    print_success "File permissions configured"
fi

# Step 10: Create database and tables
print_header "Step 10: Creating Database and Tables"

# Create database
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
print_success "Database created/verified"

# Import schema
if [ -f "database/schema.sql" ]; then
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/schema.sql
    print_success "Database schema imported"
else
    print_warning "schema.sql not found in database/ directory"
fi

# Step 11: Create initial admin user (optional)
print_header "Step 11: Create Initial Admin User"

CREATE_ADMIN=$(read_input "Create admin account now? (yes/no)" "yes")

if [ "$CREATE_ADMIN" = "yes" ]; then
    ADMIN_USERNAME=$(read_input "Admin Username" "admin")
    echo -n -e "${BLUE}Admin Password: ${NC}"
    read -rs ADMIN_PASSWORD
    echo ""
    ADMIN_NAME=$(read_input "Admin Full Name" "Administrator")
    ADMIN_PHONE=$(read_input "Admin WhatsApp Number" "081234567890")
    
    # Hash password using PHP
    ADMIN_PASS_HASH=$(php -r "echo password_hash('$ADMIN_PASSWORD', PASSWORD_BCRYPT, ['cost' => 12]);")
    
    # Insert admin user
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << MYSQL_EOF
INSERT INTO users (username, password_hash, nama_lengkap, no_wa, role, is_active, created_at) 
VALUES ('$ADMIN_USERNAME', '$ADMIN_PASS_HASH', '$ADMIN_NAME', '$ADMIN_PHONE', 'admin', 1, NOW());
MYSQL_EOF
    
    print_success "Admin account created"
    echo -e "  ${BLUE}Username: $ADMIN_USERNAME${NC}"
else
    print_info "Skipping admin account creation"
fi

# Step 12: Configure Apache (if needed)
print_header "Step 12: Apache Configuration"

if command_exists apache2ctl; then
    CONFIGURE_APACHE=$(read_input "Configure Apache? (yes/no)" "no")
    
    if [ "$CONFIGURE_APACHE" = "yes" ]; then
        # Create Apache virtual host configuration
        VHOST_FILE="/etc/apache2/sites-available/si-pusban.conf"
        
        if [ ! -f "$VHOST_FILE" ]; then
            cat > /tmp/si-pusban.conf << EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    DocumentRoot $WEB_ROOT/public
    
    <Directory $WEB_ROOT/public>
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^ index.php [QSA,L]
        </IfModule>
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/si-pusban-error.log
    CustomLog \${APACHE_LOG_DIR}/si-pusban-access.log combined
</VirtualHost>
EOF
            
            print_info "Please copy the following content to $VHOST_FILE:"
            cat /tmp/si-pusban.conf
            echo ""
            print_warning "Then run: sudo a2ensite si-pusban && sudo a2enmod rewrite && sudo systemctl restart apache2"
        fi
    fi
else
    print_info "Apache not detected. Please configure your web server manually."
fi

# Step 13: Summary and next steps
print_header "Installation Complete!"

echo ""
print_success "SI-PUSBAN has been successfully installed!"
echo ""
print_info "Configuration Summary:"
echo "  Database Host: $DB_HOST"
echo "  Database Name: $DB_NAME"
echo "  Desa Name: $DESA_NAME"
echo "  Application URL: $DOMAIN"
echo ""

print_info "Next Steps:"
echo "  1. Point your web server to: public/"
echo "  2. Navigate to: $DOMAIN"
echo "  3. Login with your admin credentials"
echo "  4. Configure cloud storage (if not done)"
echo "  5. Configure WhatsApp gateway (if needed)"
echo ""

print_info "Configuration file location: $(pwd)/.env"
print_warning "Keep your .env file secure and don't commit it to version control!"
echo ""

print_success "Installation wizard completed. Thank you for using SI-PUSBAN!"
echo ""
