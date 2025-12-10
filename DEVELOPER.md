# Panduan Pengembang SI-PUSBAN

## 📚 Dokumentasi Teknis

### 1. Arsitektur Aplikasi

SI-PUSBAN menggunakan arsitektur **MVC (Model-View-Controller)** dengan pemisahan concern yang jelas:

```
Request → Router → Controller → Model → View → Response
```

**Alur Request:**
1. User mengakses URL di browser
2. Apache mengarahkan ke `public/index.php`
3. Router menentukan halaman/action yang diminta
4. Controller mengambil data dari Model
5. View merender HTML dengan data
6. Response dikirim ke browser

### 2. Class Diagram

#### Authentication Flow
```
┌─────────┐         ┌─────────────┐        ┌──────────┐
│  User   │ ──────→ │ AuthController │ ──→ │ User Model │
│ Browser │         │               │      │            │
└─────────┘         └─────────────┘        └──────────┘
                         ↓
                    ┌──────────────┐
                    │ Validate OTP │
                    │ Hash Password│
                    │ Set Session  │
                    └──────────────┘
```

#### Usulan Management Flow
```
┌──────────┐
│  Usulan  │ ──→ ┌──────────────────┐
│  Form    │     │ UsulanController │
└──────────┘     └──────────────────┘
                       ↓
                 ┌──────────────────┐
                 │ Usulan Model     │
                 │ - Create         │
                 │ - Update Status  │
                 │ - Validate       │
                 └──────────────────┘
                       ↓
                 ┌──────────────────┐
                 │ Dokumen Model    │
                 │ - Upload         │
                 │ - Compress       │
                 │ - Cloud Upload   │
                 └──────────────────┘
```

### 3. Database Design

#### Entity Relationship Diagram

```
┌─────────────┐
│   users     │
├─────────────┤
│ id (PK)     │
│ username    │◄────┐
│ password    │     │
│ name        │     │  (1:N)
│ phone       │     │
│ role        │     │
│ is_active   │     │
└─────────────┘     │
                    │
          ┌─────────┴────────┐
          │                  │
     ┌────┴────────┐    ┌────┴────────┐
     │   usulan    │    │  activity_log│
     ├─────────────┤    ├──────────────┤
     │ id (PK)     │    │ id (PK)      │
     │ user_id (FK)│    │ user_id (FK) │
     │ nik         │    │ action       │
     │ no_kk       │    │ created_at   │
     │ status      │    └──────────────┘
     │ created_at  │
     └─────────────┘
            │ (1:N)
            │
     ┌──────┴─────────┐
     │ dokumen_usulan │
     ├────────────────┤
     │ id (PK)        │
     │ usulan_id (FK) │
     │ tipe_dokumen   │
     │ cloud_url      │
     │ created_at     │
     └────────────────┘
```

### 4. Security Implementation

#### Password Security
- **Hashing:** BCrypt dengan cost=12
- **Salt:** Auto-generated per password
- **Verification:** `password_verify()` untuk login

```php
// Registration
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Login
if (password_verify($input, $hash)) {
    // Valid
}
```

#### CSRF Protection
- **Token Generation:** 32 random bytes per session
- **Validation:** Token check pada setiap POST request
- **Expiry:** Same session lifetime

#### OTP Security
- **Format:** 6 random digits
- **Expiry:** 10 minutes (600 seconds)
- **Usage:** Once per verification
- **Storage:** Database dengan hashed code

#### File Upload Security
- **Validation:** MIME type checking
- **Size Limit:** Max 5MB
- **Whitelist:** Only image/pdf types
- **Rename:** Auto-rename dengan NIK
- **Storage:** Cloud (not on server)

### 5. API Response Format

Semua API endpoints mengembalikan response dalam format JSON konsisten:

**Success Response:**
```json
{
  "success": true,
  "message": "Operasi berhasil",
  "data": {
    "id": 123,
    "name": "John Doe"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Deskripsi error",
  "data": null
}
```

**Status Codes:**
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `500` - Server Error

### 6. Input Validation

#### Server-Side Validation (Required)
```php
// Sanitize input
$input = Auth::sanitize($_POST['field']);

// Validate format
if (!preg_match('/^\d{16}$/', $nik)) {
    return error('Invalid NIK format');
}

// Validate existence
if ($model->getUserById($id) === null) {
    return error('User not found');
}
```

#### Client-Side Validation (UX)
```javascript
// Real-time validation
form.addEventListener('change', (e) => {
    if (!validateNIK(e.target.value)) {
        showError('NIK harus 16 digit');
    }
});
```

### 7. Error Handling

#### Exception Handling Pattern
```php
try {
    // Business logic
    $result = $model->operation();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    Response::error('Database error occurred', null, 500);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    Response::error('An error occurred', null, 500);
}
```

#### Logging
```php
// Log to file
error_log("Message", 3, "storage/logs/app.log");

// Log level pattern
// INFO: Normal operations
// WARNING: Potential issues
// ERROR: Errors that need attention
// CRITICAL: System failures
```

### 8. Configuration Management

#### Environment Variables
Stored in `.env` file dan loaded di `Config.php`:

```php
// Get configuration value
$dbHost = Config::get('DB_HOST', 'localhost');

// Environment-specific configs
if (Config::get('APP_ENV') === 'production') {
    ini_set('display_errors', 0);
} else {
    ini_set('display_errors', 1);
}
```

### 9. Session Management

#### Session Configuration
```php
// Session timeout: 1 hour
define('SESSION_TIMEOUT', 3600);

// Session check
if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
    Auth::logout();
    // Redirect to login
}
```

#### Session Data Structure
```php
$_SESSION['user'] = [
    'id' => 123,
    'username' => 'john_doe',
    'nama_lengkap' => 'John Doe',
    'role' => 'admin',
    'no_wa' => '081234567890',
    'logged_in_at' => time()
];

$_SESSION['csrf_token'] = 'random_hex_string';
$_SESSION['captcha_code'] = '123456';
```

### 10. Database Query Patterns

#### Prepared Statements (Safe)
```php
// Using PDO
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Using Database helper
$user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
```

#### Avoiding SQL Injection
```php
// ✗ WRONG - Vulnerable
$query = "SELECT * FROM users WHERE id = " . $_GET['id'];

// ✓ CORRECT - Safe
$query = "SELECT * FROM users WHERE id = ?";
$params = [$_GET['id']];
$user = $db->fetchOne($query, $params);
```

### 11. File Upload Process

#### Upload Workflow
```
1. Validate file (type, size, MIME)
   ↓
2. Move to temporary storage
   ↓
3. Compress/Convert (if needed)
   ↓
4. Upload to cloud storage
   ↓
5. Save URL to database
   ↓
6. Delete temporary file
   ↓
7. Return cloud URL to client
```

#### Compression Implementation
```php
// Image compression using GD
$image = imagecreatefromjpeg($file);
imagejpeg($image, $file, 80); // 80% quality
imagedestroy($image);

// Result: ~30-40% size reduction
```

### 12. Testing Guidelines

#### Unit Testing Example
```php
// Test User registration
$user = new User();
$result = $user->register([
    'username' => 'testuser',
    'password' => 'password123',
    'nama_lengkap' => 'Test User',
    'no_wa' => '081234567890'
]);

assert($result['success'] === true);
assert($result['user_id'] > 0);
```

#### Integration Testing
```php
// Test complete usulan flow
1. User registers and verifies OTP
2. User creates usulan
3. User uploads documents
4. Admin verifies and approves
5. Check notifications sent
```

---

## 🔧 Development Setup

### Local Development Environment

```bash
# 1. Setup PHP built-in server (for testing)
cd public/
php -S localhost:8000

# 2. Access at http://localhost:8000

# 3. To use Apache instead:
# Configure virtual host as per README.md
```

### Debugging

#### Enable Debug Mode
```env
APP_DEBUG=true
```

#### View Errors
```php
// Show in browser (development only)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Better: Log to file
ini_set('log_errors', 1);
ini_set('error_log', 'storage/logs/php-errors.log');
```

#### Browser Console
```javascript
// Check API responses
fetch('/api/...')
    .then(r => r.json())
    .then(console.log);
```

---

## 📝 Coding Standards

### PHP Naming Convention
- **Classes:** `PascalCase` (e.g., `UserController`)
- **Functions:** `camelCase` (e.g., `getUserById`)
- **Constants:** `UPPER_SNAKE_CASE` (e.g., `DB_HOST`)
- **Variables:** `snake_case` (e.g., `user_id`)

### Code Organization
```php
<?php
// 1. Requires and includes at top
require_once 'path/to/file.php';

// 2. Class definition
class MyClass {
    // 2a. Properties
    private $property;
    
    // 2b. Constructor
    public function __construct() {}
    
    // 2c. Public methods
    public function publicMethod() {}
    
    // 2d. Protected methods
    protected function protectedMethod() {}
    
    // 2e. Private methods
    private function privateMethod() {}
}
?>
```

### Comments
```php
/**
 * Function description
 * @param type $param Description
 * @return type Description
 */
public function myFunction($param) {
    // Implementation
}
```

---

## 📦 Dependencies & Libraries

### Built-in PHP (No external dependencies needed)
- PDO for database
- GD for image processing
- cURL for API calls
- Session for authentication

### Frontend Dependencies
- Bootstrap 5.3 (CSS framework)
- Font Awesome 6.4 (Icons)
- Vanilla JavaScript (No jQuery)

---

## 🚀 Deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Update `APP_URL` dengan domain production
- [ ] Verify database credentials
- [ ] Setup cloud storage API keys
- [ ] Configure WhatsApp gateway
- [ ] Set strong session encryption key
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall rules
- [ ] Setup backup strategy
- [ ] Setup monitoring & logging
- [ ] Test all features on production
- [ ] Document any customizations

---

## 📞 Support & Contribution

Para berkontribusi atau melaporkan bug:
1. Create issue dengan deskripsi lengkap
2. Fork repository dan buat feature branch
3. Submit pull request dengan dokumentasi

---

**Last Updated: December 2025**
**Version: 1.0.0**
