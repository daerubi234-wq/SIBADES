<?php
/**
 * SI-PUSBAN Post-Installation Setup Script
 * Run this after installation to verify everything is working
 */

require_once __DIR__ . '/application/config/Config.php';
require_once __DIR__ . '/application/config/Database.php';

class SetupValidator {
    private $errors = [];
    private $warnings = [];
    private $success = [];

    public function run() {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  SI-PUSBAN Post-Installation Verification\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";

        $this->checkPhpVersion();
        $this->checkExtensions();
        $this->checkDirectories();
        $this->checkEnvFile();
        $this->checkDatabase();
        $this->checkFilePermissions();

        $this->printResults();
    }

    private function checkPhpVersion() {
        $version = phpversion();
        $required = '7.4';

        if (version_compare($version, $required, '>=')) {
            $this->success("PHP version $version (>= $required)");
        } else {
            $this->errors("PHP version $version is below required $required");
        }
    }

    private function checkExtensions() {
        $required = ['pdo_mysql', 'json', 'session'];
        $optional = ['gd', 'curl', 'imagick'];

        foreach ($required as $ext) {
            if (extension_loaded($ext)) {
                $this->success("PHP extension: $ext");
            } else {
                $this->errors("PHP extension missing: $ext");
            }
        }

        foreach ($optional as $ext) {
            if (extension_loaded($ext)) {
                $this->success("PHP extension: $ext (optional)");
            } else {
                $this->warnings("PHP extension missing: $ext (optional)");
            }
        }
    }

    private function checkDirectories() {
        $dirs = [
            'public',
            'application',
            'application/config',
            'application/controllers',
            'application/models',
            'application/views',
            'storage',
            'storage/uploads',
            'storage/logs',
            'database'
        ];

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $this->success("Directory exists: $dir");
            } else {
                $this->errors("Directory missing: $dir");
            }
        }
    }

    private function checkEnvFile() {
        if (file_exists('.env')) {
            $this->success("Environment file exists: .env");

            $content = file_get_contents('.env');
            $required_keys = ['DB_HOST', 'DB_USER', 'DB_NAME', 'DESA_NAME'];

            foreach ($required_keys as $key) {
                if (strpos($content, "$key=") !== false) {
                    $this->success("Configuration key: $key");
                } else {
                    $this->errors("Configuration key missing: $key");
                }
            }
        } else {
            $this->errors("Environment file missing: .env");
            echo "   → Copy .env.example to .env and configure\n";
        }
    }

    private function checkDatabase() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $this->success("Database connection successful");

            // Check if tables exist
            $tables = ['users', 'usulan', 'dokumen_usulan', 'config_desa'];
            foreach ($tables as $table) {
                $result = $db->fetchOne("SHOW TABLES LIKE ?", [$table]);
                if ($result) {
                    $this->success("Database table exists: $table");
                } else {
                    $this->errors("Database table missing: $table");
                }
            }
        } catch (Exception $e) {
            $this->errors("Database connection failed: " . $e->getMessage());
        }
    }

    private function checkFilePermissions() {
        // Check write permissions
        if (is_writable('storage/uploads')) {
            $this->success("Write permission: storage/uploads");
        } else {
            $this->errors("No write permission: storage/uploads");
        }

        if (is_writable('storage/logs')) {
            $this->success("Write permission: storage/logs");
        } else {
            $this->warnings("No write permission: storage/logs");
        }

        if (is_readable('.env')) {
            $this->success("Read permission: .env");
        } else {
            $this->errors("No read permission: .env");
        }
    }

    private function success($message) {
        $this->success[] = $message;
        echo "  ✓ $message\n";
    }

    private function errors($message) {
        $this->errors[] = $message;
        echo "  ✗ $message\n";
    }

    private function warnings($message) {
        $this->warnings[] = $message;
        echo "  ⚠ $message\n";
    }

    private function printResults() {
        echo "\n═══════════════════════════════════════════════════════════════\n";
        echo "  Verification Results\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";

        printf("✓ Success:  %d items\n", count($this->success));
        printf("⚠ Warnings: %d items\n", count($this->warnings));
        printf("✗ Errors:   %d items\n\n", count($this->errors));

        if (count($this->errors) > 0) {
            echo "❌ Installation has errors. Please fix them before using the system.\n\n";
            exit(1);
        } else if (count($this->warnings) > 0) {
            echo "⚠️  Installation complete with warnings. Some features may not work optimally.\n\n";
        } else {
            echo "✅ Installation verified successfully! You can now use SI-PUSBAN.\n\n";
            echo "Next steps:\n";
            echo "  1. Point your web server to: public/\n";
            echo "  2. Login with your admin credentials\n";
            echo "  3. Configure cloud storage (if needed)\n";
            echo "  4. Configure WhatsApp gateway (if needed)\n\n";
        }

        echo "═══════════════════════════════════════════════════════════════\n\n";
    }
}

// Run validator
if (php_sapi_name() === 'cli') {
    $validator = new SetupValidator();
    $validator->run();
} else {
    die("This script must be run from command line");
}
?>
