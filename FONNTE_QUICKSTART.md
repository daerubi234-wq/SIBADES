# 🚀 Fonnte WhatsApp OTP Integration - Quick Start

## What's New

Your SI-PUSBAN application now has **free WhatsApp OTP integration** using Fonnte!

- ✅ OTP automatically sent via WhatsApp
- ✅ OTP saved to database with 10-minute expiry
- ✅ Works for both registration and login
- ✅ Free service (20 messages/day)

## Quick Setup (2 minutes)

### Step 1: Create Fonnte Account

1. Go to **https://fonnte.com**
2. Click **"Sign Up"** → Fill your details
3. Verify email + phone
4. Get free account with 20 messages/day

### Step 2: Get API Key

1. Log in to Fonnte dashboard
2. Go to **Settings** → **API Key**
3. Copy your API Key

### Step 3: Configure Application

Edit `.env` file and find this line:

```env
FONNTE_API_KEY=your_fonnte_api_key_here
```

Replace with your actual API key:

```env
FONNTE_API_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Step 4: Test It!

1. Open browser: **http://localhost:8000/index.php?page=register**
2. Fill registration form
3. Check your **WhatsApp** for OTP message
4. Enter OTP to complete registration

## How It Works

```
Registration Form
       ↓
Validate CSRF + Captcha
       ↓
Save User to Database
       ↓
Generate 6-digit OTP
       ↓
Save OTP to Database (10 min expiry)
       ↓
Send OTP via Fonnte WhatsApp API
       ↓
User receives OTP on WhatsApp (~2-3 sec)
       ↓
User enters OTP in verification page
       ↓
OTP validated against database
       ↓
User account activated ✓
```

## OTP Message Example

```
🔐 *Kode OTP Anda*

Kode: *123456*

Kode ini berlaku selama 10 menit.
Jangan berikan kode ini kepada siapapun.

—
SI-PUSBAN
Sistem Informasi Pendataan Usulan Bantuan Sosial
```

## Phone Numbers Accepted

All these formats work automatically:

- `081234567890` (standard Indonesia)
- `+6281234567890` (international)
- `6281234567890` (prefix only)
- `0081234567890` (double 0)

System normalizes to: `62xxxxxxxxxx`

## Database Storage

OTP data stored in `otp_verification` table:

```sql
SELECT * FROM otp_verification 
WHERE no_wa = '6281234567890' 
AND is_verified = FALSE
AND expires_at > NOW();
```

Fields:
- `id` - Record ID
- `user_id` - Associated user ID (null before verification)
- `no_wa` - Phone number
- `otp_code` - 6-digit OTP
- `is_verified` - Verification status
- `attempt_count` - Failed attempts
- `created_at` - Creation timestamp
- `expires_at` - Expiry time (10 minutes)

## Code Files Modified

| File | Change |
|------|--------|
| `application/services/WhatsAppService.php` | ✨ NEW - Fonnte service |
| `application/config/Auth.php` | Updated `sendOTPViaWhatsApp()` |
| `application/config/Config.php` | Added Fonnte constants |
| `.env` | Added `FONNTE_API_KEY` |

## Troubleshooting

### "Invalid API Key" Error
- Copy API key again from Fonnte dashboard
- No spaces before/after the key in `.env`
- Save `.env` and restart server

### Message Not Received
- Check phone number format: `62xxxxxxxxxx`
- Check Fonnte quota not exceeded (20/day limit)
- Wait 2-3 seconds for delivery
- Check server logs: `tail -f storage/logs/error.log`

### "FONNTE_API_KEY not configured"
- Make sure you added it to `.env` file
- Check spelling: `FONNTE_API_KEY` (exact case)
- Restart PHP server: `pkill -f "php -S"`

## Important Notes

1. **API Key is Secret** - Never commit `.env` to git
2. **Free Quota** - 20 messages/day, resets at 00:00 Jakarta time
3. **OTP Expiry** - 10 minutes by default (configurable in Config.php)
4. **Phone Validation** - Must be valid Indonesian number (9-12 digits)
5. **Database Required** - OTP verification table must exist

## Upgrade Plans

If you need more OTP messages:

- **Free:** 20/day (~600/month)
- **Starter:** ~Rp 50k/month → 1,000/month
- **Pro:** ~Rp 150k/month → 5,000/month
- **Enterprise:** Custom quota

Visit https://fonnte.com/pricing

## Documentation

- **Complete Setup:** See `FONNTE_SETUP.md`
- **Service Code:** `application/services/WhatsAppService.php`
- **Config:** `application/config/Config.php`

## Testing

Run status check:
```bash
php test_fonnte_setup.php
```

Should show:
```
✓ Fonnte API Key configured
✓ WhatsAppService found
✓ otp_verification table exists in database
```

## Next Steps

1. ✅ Get Fonnte API key (https://fonnte.com)
2. ✅ Update `.env` with `FONNTE_API_KEY`
3. ✅ Test registration with real WhatsApp number
4. ✅ Verify OTP arrives and works
5. ✅ Deploy to production

## Support

- **Fonnte Docs:** https://fonnte.com/docs
- **Fonnte Dashboard:** https://app.fonnte.com
- **WhatsApp Direct:** Available in Fonnte dashboard

---

**Status:** ✓ Implementation Complete | Ready for Configuration

**Last Updated:** December 2025
