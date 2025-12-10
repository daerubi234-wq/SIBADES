<?php
/**
 * Dokumen (Document) Model
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Config.php';

class Dokumen {
    private $db;
    private $table = 'dokumen_usulan';
    private $uploadPath;
    private $requiredDocs = ['KTP', 'KK', 'RMH_DEPAN', 'RMH_SAMPING', 'RMH_DALAM'];

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->uploadPath = __DIR__ . '/../../' . Config::UPLOAD_PATH;
    }

    /**
     * Upload and save document
     * Handles compression, conversion, and cloud storage
     */
    public function uploadDocument($usulanId, $tipeDocumen, $file, $nik, $namaLengkap) {
        // Validate file
        $validation = $this->validateFile($file, $tipeDocumen);
        if (!$validation['success']) {
            return $validation;
        }

        try {
            // Create temp directory if not exists
            if (!is_dir($this->uploadPath)) {
                mkdir($this->uploadPath, 0775, true);
            }

            // Process file (compress/convert)
            $processedFile = $this->processFile($file, $tipeDocumen);
            if (!$processedFile) {
                return ['success' => false, 'message' => 'Gagal memproses file'];
            }

            // Generate cloud filename
            $cloudFileName = $this->generateCloudFileName($nik, $namaLengkap, $tipeDocumen, $processedFile['extension']);

            // Upload to cloud
            $cloudUrl = $this->uploadToCloud($processedFile['path'], $cloudFileName);
            if (!$cloudUrl) {
                return ['success' => false, 'message' => 'Gagal mengunggah ke cloud'];
            }

            // Save document record to database
            $docData = [
                'usulan_id' => $usulanId,
                'tipe_dokumen' => $tipeDocumen,
                'cloud_url' => $cloudUrl,
                'nama_file_asli' => $file['name'],
                'nama_file_di_cloud' => $cloudFileName,
                'ukuran_file' => filesize($processedFile['path']),
                'tipe_file' => $processedFile['extension']
            ];

            $database = new Database();
            $docId = $database->insert($this->table, $docData);

            // Delete temporary file
            if (file_exists($processedFile['path'])) {
                unlink($processedFile['path']);
            }

            return ['success' => true, 'message' => 'Dokumen berhasil diunggah', 'doc_id' => $docId, 'cloud_url' => $cloudUrl];
        } catch (Exception $e) {
            error_log("Upload document error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengunggah dokumen'];
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file, $tipeDocumen) {
        // Check if file exists
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'File tidak valid'];
        }

        // Check file size
        if ($file['size'] > Config::MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'Ukuran file terlalu besar (max 5MB)'];
        }

        // Get file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate file type
        if ($tipeDocumen === 'KK') {
            // KK must be image (will be converted to PDF)
            if (!in_array($ext, Config::ALLOWED_IMAGE_TYPES)) {
                return ['success' => false, 'message' => 'Format file KK harus gambar (JPG, PNG, GIF)'];
            }
        } else {
            // Other documents must be image
            if (!in_array($ext, Config::ALLOWED_IMAGE_TYPES)) {
                return ['success' => false, 'message' => 'Format file harus gambar (JPG, PNG, GIF)'];
            }
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'message' => 'Tipe file tidak didukung'];
        }

        return ['success' => true];
    }

    /**
     * Process file (compress images, convert KK to PDF)
     */
    private function processFile($file, $tipeDocumen) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $tempFileName = 'temp_' . time() . '_' . uniqid() . '.' . $ext;
        $tempPath = $this->uploadPath . $tempFileName;

        // Move uploaded file to temp storage
        if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
            return false;
        }

        // Compress image
        if ($this->isImageFile($ext)) {
            $compressed = $this->compressImage($tempPath, $ext);
            if (!$compressed) {
                unlink($tempPath);
                return false;
            }
            $tempPath = $compressed;
        }

        // Convert KK to PDF if needed
        if ($tipeDocumen === 'KK' && $ext !== 'pdf') {
            $pdfPath = $this->imageToPdf($tempPath);
            if ($pdfPath) {
                unlink($tempPath);
                return ['path' => $pdfPath, 'extension' => 'pdf'];
            }
        }

        return ['path' => $tempPath, 'extension' => $ext];
    }

    /**
     * Compress image
     */
    private function compressImage($imagePath, $ext) {
        try {
            // Check if GD is available
            if (!extension_loaded('gd')) {
                error_log("GD extension not loaded");
                return $imagePath; // Return original if GD not available
            }

            $image = null;

            // Load image based on type
            switch (strtolower($ext)) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($imagePath);
                    break;
                case 'png':
                    $image = imagecreatefrompng($imagePath);
                    break;
                case 'gif':
                    $image = imagecreatefromgif($imagePath);
                    break;
            }

            if (!$image) {
                return $imagePath;
            }

            // Save compressed image
            if (in_array(strtolower($ext), ['jpg', 'jpeg'])) {
                imagejpeg($image, $imagePath, Config::IMAGE_QUALITY);
            } else {
                imagepng($image, $imagePath, 8);
            }

            imagedestroy($image);
            return $imagePath;
        } catch (Exception $e) {
            error_log("Compress image error: " . $e->getMessage());
            return $imagePath; // Return original if compression fails
        }
    }

    /**
     * Convert image to PDF
     * This is a placeholder - requires external library or service
     */
    private function imageToPdf($imagePath) {
        try {
            // Check if imagick is available
            if (extension_loaded('imagick')) {
                $imagick = new Imagick($imagePath);
                $imagick->setImageFormat('pdf');
                
                $pdfPath = str_replace(pathinfo($imagePath, PATHINFO_EXTENSION), 'pdf', $imagePath);
                $imagick->writeImage($pdfPath);
                $imagick->destroy();
                
                return $pdfPath;
            } else {
                // Fallback: keep as image for now, can implement external service later
                error_log("Imagick extension not loaded - KK conversion skipped");
                return $imagePath;
            }
        } catch (Exception $e) {
            error_log("Image to PDF conversion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if file is image
     */
    private function isImageFile($ext) {
        return in_array(strtolower($ext), Config::ALLOWED_IMAGE_TYPES);
    }

    /**
     * Generate cloud filename using NIK and document type
     */
    private function generateCloudFileName($nik, $namaLengkap, $tipeDocumen, $ext) {
        $sanitizedName = preg_replace('/[^A-Za-z0-9_-]/', '_', $namaLengkap);
        return "{$nik}_{$sanitizedName}_{$tipeDocumen}.{$ext}";
    }

    /**
     * Upload file to cloud storage
     * Placeholder for cloud service integration (Google Drive, Mega, etc.)
     */
    private function uploadToCloud($filePath, $fileName) {
        try {
            $cloudProvider = Config::get('CLOUD_PROVIDER', 'google_drive');
            
            switch ($cloudProvider) {
                case 'google_drive':
                    return $this->uploadToGoogleDrive($filePath, $fileName);
                case 'mega':
                    return $this->uploadToMega($filePath, $fileName);
                default:
                    return $this->uploadToGoogleDrive($filePath, $fileName);
            }
        } catch (Exception $e) {
            error_log("Upload to cloud error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Upload to Google Drive (placeholder)
     */
    private function uploadToGoogleDrive($filePath, $fileName) {
        // TODO: Implement Google Drive API integration
        // For now, return a placeholder URL
        // In production, use Google Drive API with OAuth2
        
        $folderId = Config::get('CLOUD_FOLDER_ID');
        if (!$folderId) {
            error_log("Google Drive folder ID not configured");
            return false;
        }

        // Placeholder: return dummy URL structure
        // In real implementation, use curl to call Google Drive API
        $fileId = bin2hex(random_bytes(8));
        return "https://drive.google.com/file/d/{$fileId}/view?usp=sharing";
    }

    /**
     * Upload to Mega (placeholder)
     */
    private function uploadToMega($filePath, $fileName) {
        // TODO: Implement Mega API integration
        // For now, return a placeholder URL
        
        $fileKey = bin2hex(random_bytes(16));
        return "https://mega.nz/file/{$fileKey}";
    }

    /**
     * Get all documents for an usulan
     */
    public function getDocumentsByUsulan($usulanId) {
        $database = new Database();
        return $database->fetchAll("SELECT * FROM {$this->table} WHERE usulan_id = ? ORDER BY tipe_dokumen", [$usulanId]);
    }

    /**
     * Check if all required documents are uploaded
     */
    public function checkAllDocumentsUploaded($usulanId) {
        $database = new Database();
        $uploaded = $database->fetchAll("SELECT DISTINCT tipe_dokumen FROM {$this->table} WHERE usulan_id = ?", [$usulanId]);
        
        $uploadedTypes = array_column($uploaded, 'tipe_dokumen');
        $missing = array_diff($this->requiredDocs, $uploadedTypes);
        
        return [
            'all_uploaded' => empty($missing),
            'uploaded' => $uploadedTypes,
            'missing' => $missing
        ];
    }

    /**
     * Delete document
     */
    public function deleteDocument($docId) {
        try {
            $database = new Database();
            $doc = $database->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$docId]);
            
            if (!$doc) {
                return ['success' => false, 'message' => 'Dokumen tidak ditemukan'];
            }

            // Delete from cloud storage if needed (TODO: implement)
            // For now, just remove from database
            
            $database->delete($this->table, ['id' => $docId]);
            return ['success' => true, 'message' => 'Dokumen berhasil dihapus'];
        } catch (Exception $e) {
            error_log("Delete document error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menghapus dokumen'];
        }
    }

    /**
     * Get document by ID
     */
    public function getDocumentById($docId) {
        $database = new Database();
        return $database->fetchOne("SELECT * FROM {$this->table} WHERE id = ?", [$docId]);
    }
}
?>
