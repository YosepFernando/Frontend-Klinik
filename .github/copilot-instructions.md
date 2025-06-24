# Copilot Instructions untuk Aplikasi Klinik

<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

## Konteks Aplikasi
Ini adalah aplikasi Laravel untuk manajemen klinik dengan sistem multi-role dan fitur lengkap.

### Role yang tersedia:
- Admin: Manajemen penuh sistem
- Front Office: Manajemen pelanggan dan appointment
- Pelanggan: Booking treatment, melihat jadwal
- Kasir: Pembayaran dan transaksi
- Dokter: Jadwal treatment, catatan medis
- Beautician: Treatment dan layanan kecantikan
- HRD: Manajemen karyawan, recruitment, absensi, pelatihan

### Fitur Utama:
1. Authentication (login, register, forgot password)
2. User Management dengan role-based access
3. Recruitment System
4. Attendance Management
5. Treatment Scheduling
6. Training Management
7. Religious Study Sessions (Pengajian)

### Tech Stack:
- Backend: Laravel 12
- Frontend: Bootstrap 5, Blade templates
- Database: SQLite (development)
- CSS Framework: Bootstrap dengan custom styling
- JavaScript: Vanilla JS + Laravel Mix

### Coding Guidelines:
- Gunakan Blade components untuk UI yang reusable
- Implement middleware untuk role-based access control
- Gunakan Laravel's built-in validation
- Ikuti PSR-12 coding standards
- Gunakan resource controllers untuk CRUD operations
- Implement proper error handling dan logging
