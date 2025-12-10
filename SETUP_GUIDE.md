# 🚀 SI-PUSBAN - Quick Setup Guide

Panduan instalasi cepat SI-PUSBAN dengan setup wizard otomatis.

## ⚡ Instalasi Super Mudah (3 Langkah)

### Langkah 1: Jalankan Setup Wizard

```bash
bash setup-wizard.sh
```

### Langkah 2: Ikuti Pertanyaan Wizard

Wizard akan bertanya:
- **Nama Desa** (default: Kronjo)
- **Kecamatan** (default: Kronjo)
- **Kabupaten** (default: Tangerang)
- **Provinsi** (default: Banten)
- **Tahun Anggaran** (default: 2025)
- **Fonnte API Key** (opsional, bisa nanti)

### Langkah 3: Buka Browser

Setelah wizard selesai, buka:
```
http://localhost:8000
```

✅ Selesai! SI-PUSBAN sudah siap pakai!

---

## 📋 Apa yang Dilakukan Wizard?

Setup wizard secara otomatis:

✓ Memeriksa persyaratan sistem (PHP, Docker, cURL)  
✓ Membuat database MySQL di Docker  
✓ Mengimport database schema  
✓ Membuat file `.env` dengan konfigurasi  
✓ Membuat direktori storage (logs, uploads)  
✓ Menjalankan PHP server  
✓ Verifikasi semua komponen  

**Total waktu: 5-10 menit**

---

## 🔧 Persyaratan Minimal

- PHP 7.4+ (dengan extensions: curl, json, session, pdo_mysql)
- Docker (untuk MySQL)
- cURL (untuk Fonnte WhatsApp API)
- Internet connection

---

## 🎯 Penggunaan Fonnte WhatsApp (Opsional)

Fonnte adalah layanan gratis untuk mengirim WhatsApp:

### Dapatkan API Key (Gratis)

1. Kunjungi https://fonnte.com
2. Daftar akun (gratis)
3. Verifikasi email + nomor HP
4. Dapatkan 20 pesan gratis/hari
5. Copy API Key dari dashboard

### Konfigurasi di SI-PUSBAN

**Opsi 1: Saat Setup (Recommended)**
```bash
bash setup-wizard.sh
# Masukkan Fonnte API Key saat diminta
```

**Opsi 2: Manual (Setelah Setup)**
1. Edit file `.env`
2. Cari: `FONNTE_API_KEY=your_fonnte_api_key_here`
3. Ganti dengan API Key Anda
4. Save dan reload aplikasi

### Cara Kerja OTP WhatsApp

```
User Daftar
    ↓
Isi Form Registrasi
    ↓
Sistem Generate OTP
    ↓
OTP Terkirim ke WhatsApp (~2-3 detik)
    ↓
User Masukkan OTP
    ↓
Akun Aktif ✓
```

---

## 🔐 Database Credentials

Setelah setup, database siap dengan:

```
Host     : 127.0.0.1
Port     : 3307
User     : root
Password : root
Database : si_pusban
```

**Untuk akses MySQL:**

```bash
docker exec -it sibades-db mysql -u root -proot si_pusban
```

---

## 🚀 Jalankan Server

Setelah setup wizard selesai, server sudah running di:

```
http://localhost:8000
```

### Jika ingin start ulang:

```bash
cd /workspaces/SIBADES
/usr/bin/php -S localhost:8000 public/router.php
```

---

## 📚 Dokumentasi Lengkap

Untuk detail lebih lanjut, lihat:

- **FONNTE_QUICKSTART.md** - Quick reference (2 menit)
- **FONNTE_SETUP.md** - Setup lengkap + troubleshooting (15 menit)
- **IMPLEMENTATION_CHECKLIST.md** - Status dan checklist

---

## ❓ FAQ

### Q: Saya tidak punya Fonnte API Key, apa yang terjadi?
**A:** Sistem masih bisa jalan tanpa Fonnte. OTP hanya tidak akan terkirim via WhatsApp. Anda bisa tambahkan nanti.

### Q: Bagaimana jika setup gagal?
**A:** Lihat output error dan check FONNTE_SETUP.md bagian Troubleshooting.

### Q: Bisa di-reset ulang?
**A:** Ya, jalankan `setup-wizard.sh` lagi. Anda akan diminta konfirmasi sebelum reset.

### Q: Database tersimpan di mana?
**A:** Di Docker container `sibades-db`. Data persist meskipun container di-restart.

### Q: Bagaimana deploy ke production?
**A:** Lihat IMPLEMENTATION_CHECKLIST.md bagian "Production Deployment".

---

## 🛠 Troubleshooting Cepat

### Setup Gagal di Step Database?
```bash
# Check Docker status
docker ps

# Restart container
docker restart sibades-db

# Check logs
docker logs sibades-db
```

### Server tidak bisa akses?
```bash
# Check apakah port 8000 sudah terpakai
lsof -i :8000

# Jalankan dengan port lain
/usr/bin/php -S localhost:8001 public/router.php
```

### .env tidak terbaca?
```bash
# Check file permissions
ls -la .env

# Check konfigurasi
grep DESA_NAME .env
```

---

## 📞 Support

- **Setup Help:** FONNTE_SETUP.md → Troubleshooting
- **API Docs:** https://fonnte.com/docs
- **Fonnte Support:** Available di dashboard Fonnte

---

## 📝 Notes

- Setup wizard membuat file `.env` otomatis
- Semua konfigurasi bisa diedit manual di `.env` setelah setup
- Database secara otomatis backup-able via Docker
- Logs tersimpan di `storage/logs/`
- Uploads tersimpan di `storage/uploads/`

---

**Status:** ✅ Ready for Production  
**Last Updated:** December 10, 2025  
**Version:** 1.0

Selamat menggunakan SI-PUSBAN! 🎉
