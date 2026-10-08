# Website E-Learning Tiga Melati

Platform E-Learning Tiga Melati (Backend Laravel API & Web Application).

## Tentang Proyek
Proyek ini merupakan sistem backend dan manajemen e-learning untuk Tiga Melati yang dibangun menggunakan framework [Laravel](https://laravel.com).

## Prasyarat
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL / MariaDB

## Instalasi
1. Clone repositori:
   ```bash
   git clone https://github.com/Muhamadsolehs/Website-ElearningTigaMelati.git
   ```
2. Masuk ke folder proyek dan install dependensi:
   ```bash
   composer install
   npm install
   ```
3. Salin file `.env` dan generate key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Jalankan migrasi database:
   ```bash
   php artisan migrate
   ```
5. Jalankan server:
   ```bash
   php artisan serve
   ```
