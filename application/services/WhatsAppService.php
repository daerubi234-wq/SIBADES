<?php
/**
 * WhatsApp Service - Fonnte Integration
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 * 
 * Fonnte is a free WhatsApp gateway service: https://fonnte.com
 */

class WhatsAppService {
    private $apiKey;
    private $gatewayUrl = 'https://api.fonnte.com/send';
    private $webhookUrl = 'https://api.fonnte.com/webhook';

    public function __construct($apiKey = null) {
        require_once __DIR__ . '/../config/Config.php';
        
        $this->apiKey = $apiKey ?? Config::get('FONNTE_API_KEY');
        
        if (!$this->apiKey) {
            throw new Exception('FONNTE_API_KEY tidak dikonfigurasi dalam .env');
        }
    }

    /**
     * Send OTP via WhatsApp
     * 
     * @param string $phoneNumber Phone number in format 62xxxxxxxxxx (without + or 0)
     * @param string $otp OTP code (6 digits)
     * @return array Response with 'success' and 'message' keys
     */
    public function sendOTP($phoneNumber, $otp) {
        // Normalize phone number to Fonnte format (62xxxxxxxxxx)
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);
        
        if (!$phoneNumber) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tidak valid',
                'error' => 'INVALID_PHONE'
            ];
        }

        $message = $this->buildOTPMessage($otp);

        try {
            $response = $this->sendMessage($phoneNumber, $message);
            return $response;
        } catch (Exception $e) {
            error_log("WhatsApp OTP send error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengirim OTP ke WhatsApp',
                'error' => 'SEND_FAILED',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Send custom message via WhatsApp
     * 
     * @param string $phoneNumber Phone number
     * @param string $message Message to send
     * @return array Response
     */
    public function sendMessage($phoneNumber, $message) {
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);
        
        if (!$phoneNumber) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tidak valid'
            ];
        }

        $payload = [
            'target' => $phoneNumber,
            'message' => $message
        ];

        return $this->makeRequest($payload);
    }

    /**
     * Send OTP with retry logic
     * 
     * @param string $phoneNumber Phone number
     * @param string $otp OTP code
     * @param int $maxRetries Maximum retry attempts
     * @return array Response
     */
    public function sendOTPWithRetry($phoneNumber, $otp, $maxRetries = 3) {
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $response = $this->sendOTP($phoneNumber, $otp);
            
            if ($response['success']) {
                return $response;
            }

            // Log failed attempt
            error_log("WhatsApp OTP send attempt {$attempt}/{$maxRetries} failed: " . 
                      json_encode($response));

            // Wait before retry (exponential backoff)
            if ($attempt < $maxRetries) {
                sleep(2 ** ($attempt - 1)); // 1s, 2s, 4s
            }
        }

        return $response; // Return last attempt response
    }

    /**
     * Make HTTP request to Fonnte API
     * 
     * @param array $payload Request payload
     * @return array Response
     */
    private function makeRequest($payload) {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->gatewayUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->apiKey,
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            throw new Exception("cURL Error: {$error}");
        }

        // Parse response
        $data = json_decode($response, true);

        // Fonnte returns: { "status": true/false, "data": {...} }
        if ($httpCode === 200 && $data && ($data['status'] === true || $data['status'] === 'true')) {
            return [
                'success' => true,
                'message' => 'OTP berhasil dikirim ke WhatsApp',
                'data' => $data['data'] ?? null
            ];
        }

        return [
            'success' => false,
            'message' => $data['reason'] ?? 'Gagal mengirim pesan WhatsApp',
            'error' => 'API_ERROR',
            'http_code' => $httpCode,
            'response' => $data
        ];
    }

    /**
     * Build OTP message
     * 
     * @param string $otp OTP code
     * @param int $expiryMinutes Expiry time in minutes
     * @return string Formatted message
     */
    private function buildOTPMessage($otp, $expiryMinutes = 10) {
        return "🔐 *Kode OTP Anda*\n\n" .
               "Kode: *{$otp}*\n\n" .
               "Kode ini berlaku selama {$expiryMinutes} menit.\n" .
               "Jangan berikan kode ini kepada siapapun.\n\n" .
               "—\n" .
               "SI-PUSBAN\n" .
               "Sistem Informasi Pendataan Usulan Bantuan Sosial";
    }

    /**
     * Normalize phone number to Fonnte format
     * Accepts: 081234567890, 0081234567890, +6281234567890, 6281234567890
     * Returns: 6281234567890
     * 
     * @param string $phoneNumber Raw phone number
     * @return string|false Normalized phone number or false if invalid
     */
    private function normalizePhoneNumber($phoneNumber) {
        // Remove all non-digit characters except +
        $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);

        // Handle +62 format
        if (strpos($phoneNumber, '+62') === 0) {
            return '62' . substr($phoneNumber, 3);
        }

        // Handle 62 format (already correct)
        if (strpos($phoneNumber, '62') === 0) {
            return $phoneNumber;
        }

        // Handle 0 format (Indonesia standard)
        if (strpos($phoneNumber, '0') === 0) {
            return '62' . substr($phoneNumber, 1);
        }

        // Invalid format
        return false;
    }

    /**
     * Verify webhook signature from Fonnte
     * 
     * @param string $payload Raw request body
     * @param string $signature Signature from header
     * @return bool True if signature is valid
     */
    public function verifyWebhookSignature($payload, $signature) {
        // Fonnte uses HMAC-SHA256 for webhook signature
        // signature = HMAC-SHA256(payload, API_KEY)
        
        $calculatedSignature = hash_hmac('sha256', $payload, $this->apiKey);
        
        return hash_equals($calculatedSignature, $signature);
    }

    /**
     * Get remaining quota from Fonnte
     * 
     * @return array Quota information
     */
    public function getQuota() {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/info',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);
        return $data ?? ['success' => false];
    }
}
?>
