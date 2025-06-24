# Aplikasi Klinik - Laravel Frontend

Aplikasi web management klinik yang dibangun dengan Laravel, dilengkapi dengan sistem multi-role dan fitur lengkap untuk pengelolaan klinik modern.

## 🚀 Fitur Utama

### 👥 Sistem Multi-Role
- **Admin**: Akses penuh ke semua fitur sistem
- **Front Office**: Manajemen pelanggan dan appointment
- **Pelanggan**: Booking treatment dan melihat jadwal
- **Kasir**: Pembayaran dan transaksi
- **Dokter**: Jadwal treatment dan catatan medis
- **Beautician**: Treatment dan layanan kecantikan
- **HRD**: Manajemen karyawan, recruitment, absensi, pelatihan

### 🏥 Fitur Aplikasi
1. **Authentication System**
   - Login & Register
   - Forgot Password
   - Role-based Access Control

2. **Management Treatment**
   - CRUD Treatment
   - Kategori: Medical, Beauty, Wellness
   - Harga dan durasi treatment

3. **Jadwal Treatment**
   - Booking appointment
   - Konfirmasi jadwal
   - Status tracking

4. **Sistem Absensi**
   - Check-in/Check-out
   - Laporan kehadiran
   - Status kehadiran (hadir, sakit, izin, terlambat)

5. **Recruitment System**
   - Posting lowongan kerja
   - Aplikasi lowongan
   - Management kandidat

6. **Training Management**
   - Buat program pelatihan
   - Pendaftaran peserta
   - Evaluasi dan feedback

7. **Pengajian (Religious Study)**
   - Jadwal pengajian
   - Pendaftaran peserta
   - Management pembicara

## 🛠️ Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Bootstrap 5, Blade Templates
- **Database**: SQLite (development)
- **CSS Framework**: Bootstrap dengan custom styling
- **Icons**: Bootstrap Icons
- **JavaScript**: Vanilla JS

## 📋 Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- SQLite

## 🚀 Installation

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Compile Assets**
   ```bash
   npm run dev
   ```

5. **Run Application**
   ```bash
   php artisan serve
   ```

   Aplikasi akan berjalan di: `http://localhost:8000`

## 👤 Default Users

Setelah running seeder, Anda dapat login dengan:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@klinik.com | password |
| Front Office | frontoffice@klinik.com | password |
| Dokter | dokter@klinik.com | password |
| Beautician | beautician@klinik.com | password |
| Kasir | kasir@klinik.com | password |
| HRD | hrd@klinik.com | password |
| Pelanggan | customer@klinik.com | password |

## 🔧 Development Commands

```bash
# Run Laravel server
php artisan serve

# Compile assets (development)
npm run dev

# Compile assets (production)
npm run build

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name

# Create new model
php artisan make:model ModelName

# Create new controller
php artisan make:controller ControllerName
```

---

**Klinik App** - Modernizing healthcare management with technology 🏥✨
