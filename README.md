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
