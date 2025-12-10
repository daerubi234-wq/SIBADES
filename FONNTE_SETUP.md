# Fonnte WhatsApp Integration Guide

## Overview

This application now integrates with **Fonnte**, a free WhatsApp gateway service. Fonnte allows sending WhatsApp messages programmatically using their API.

**Key Features:**
- ✅ Free service (20 messages/day on free account)
- ✅ No phone number spoofing
- ✅ Easy API integration
- ✅ Webhook support for incoming messages
- ✅ Reliable message delivery

## Setup Instructions

### Step 1: Create Fonnte Account

1. Visit **https://fonnte.com**
2. Click "Sign Up" or "Daftar"
3. Fill in your details:
   - Email
   - Password
   - Phone number (for verification)
4. Verify your email and phone number
5. You'll get a free account with 20 messages/day quota

### Step 2: Get API Key

1. Log in to your Fonnte dashboard
2. Go to **Dashboard** → **Settings** or **API Key**
3. You'll see your API Key (looks like: `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...`)
4. Copy your API Key

### Step 3: Configure Application

1. Open `.env` file in the project root:
   ```bash
   nano .env
   ```

2. Find and update the Fonnte configuration:
   ```env
   # WhatsApp Gateway - Fonnte (Free)
   FONNTE_API_KEY=your_actual_api_key_here
   ```

3. Save the file (Ctrl+X, then Y, then Enter in nano)

### Step 4: Test the Integration

Run the test script to verify the connection:

```bash
php /tmp/test_fonnte_otp.php
```

Expected output:
```
✓ Fonnte API Key configured
✓ WhatsApp service initialized successfully
✓ OTP generated: 123456
✓ OTP will be sent to: 62812xxxxx
✓ Send to WhatsApp: Success!
✓ OTP saved to database
```

## How It Works

### OTP Flow During Registration

1. User fills registration form and submits
2. Application generates 6-digit random OTP
3. OTP is saved to database with:
   - Phone number
   - Expiry time (10 minutes)
   - Verification status (false initially)
4. Fonnte WhatsApp service sends OTP to user's WhatsApp
5. User receives WhatsApp message with OTP code
6. User enters OTP in verification form
7. Application verifies OTP from database
8. User account is activated

### OTP Flow During Login (Optional)

If 2FA with OTP is enabled:

1. User enters username/password
2. Credentials are validated
3. If valid, OTP is generated
4. OTP sent via WhatsApp
5. User enters OTP to complete login

## Database Schema

OTP data is stored in `otp_verification` table:

```sql
CREATE TABLE `otp_verification` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NULL,
  `no_wa` VARCHAR(15) NOT NULL,
  `otp_code` VARCHAR(6) NOT NULL,
  `is_verified` BOOLEAN DEFAULT FALSE,
  `attempt_count` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);
```

## Message Format

The OTP message sent to WhatsApp looks like:

```
🔐 *Kode OTP Anda*

Kode: *123456*

Kode ini berlaku selama 10 menit.
Jangan berikan kode ini kepada siapapun.

—
SI-PUSBAN
Sistem Informasi Pendataan Usulan Bantuan Sosial
```

## Phone Number Formats Accepted

The system accepts phone numbers in these formats:

- `081234567890` (Indonesia standard)
- `+6281234567890` (International format)
- `6281234567890` (Prefix only)
- `0081234567890` (Double 0 format)

All formats are automatically normalized to `62xxxxxxxxxx` for Fonnte API.

## API Response Handling

### Successful Send
```json
{
  "success": true,
  "message": "OTP berhasil dikirim ke WhatsApp",
  "data": {
    "queue_id": "xxx",
    "status": "pending"
  }
}
```

### Failed Send
```json
{
  "success": false,
  "message": "Gagal mengirim OTP ke WhatsApp",
  "error": "API_ERROR",
  "http_code": 401,
  "response": {
    "status": false,
    "reason": "Invalid API Key"
  }
}
```

## Troubleshooting

### Issue: "FONNTE_API_KEY tidak dikonfigurasi"

**Solution:** 
- Check that you've added `FONNTE_API_KEY` to `.env` file
- Restart the PHP server: `pkill -f "php -S"` then start again

### Issue: "Invalid API Key" error

**Solution:**
- Verify API key is correct (copy from Fonnte dashboard again)
- Check for extra spaces before/after the key in `.env`
- API key should be in format: `eyJhbGc...` (long JWT-like string)

### Issue: Message not received on WhatsApp

**Possible causes:**
1. Phone number format incorrect (must be valid Indonesian number)
2. Free quota exceeded (20 messages/day limit)
3. Recipient phone number not in Fonnte's whitelist
4. Network/API connectivity issues

**Solutions:**
1. Verify phone number format: `62812xxxxx`
2. Check Fonnte dashboard for quota status
3. Add recipient to whitelist in Fonnte dashboard
4. Check server logs: `tail -f storage/logs/error.log`

### Issue: "cURL Error" in logs

**Possible causes:**
- Server cannot reach Fonnte API (firewall/network issue)
- SSL certificate verification failing

**Solutions:**
1. Check internet connectivity: `curl https://api.fonnte.com`
2. Verify firewall allows outbound HTTPS connections
3. Check PHP cURL extension is enabled: `php -m | grep curl`

## Quota Management

Free Fonnte account includes:
- **20 messages/day** limit
- Message quota resets at **00:00 UTC+7 (Jakarta time)**

To check remaining quota:
```php
$whatsapp = new WhatsAppService();
$quota = $whatsapp->getQuota();
echo "Remaining quota: " . $quota['data']['quota'] ?? "N/A";
```

## Upgrade Options

If you need more messages:

1. **Free Account:** 20 messages/day
2. **Starter:** ~Rp 50k/month → 1,000 messages
3. **Professional:** ~Rp 150k/month → 5,000 messages
4. **Enterprise:** Custom quota

Visit https://fonnte.com/pricing for details.

## Security Notes

1. **Never share your API key** - Keep it secret in `.env`
2. **OTP Expiry:** Set to 10 minutes for security
3. **Rate limiting:** Consider implementing to prevent abuse
4. **Webhook verification:** If using webhooks, always verify signatures
5. **Phone validation:** System validates phone format before sending

## API Documentation

For complete Fonnte API documentation, visit:
- **Official Docs:** https://fonnte.com/docs
- **Dashboard:** https://app.fonnte.com

## Code Files Modified

- `application/services/WhatsAppService.php` - New service for Fonnte integration
- `application/config/Auth.php` - Updated to use WhatsAppService
- `application/config/Config.php` - Added Fonnte configuration constants
- `.env` - Added FONNTE_API_KEY configuration

## Next Steps

1. Get Fonnte API key from https://fonnte.com
2. Update `FONNTE_API_KEY` in `.env` file
3. Test by creating a new user account
4. OTP should arrive via WhatsApp within 2-3 seconds
5. Complete verification to activate account

---

**Last Updated:** December 2025
**Status:** ✓ Production Ready
