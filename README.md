# Clinic Human Resource Management System

Web-Based Human Resource Information System, Laravel.

Back-end can be accessed [here](https://github.com/YosepFernando/Backend-Klinik)

## Main Features

### Roles
- **Admin**: Full Access
- **Employee**: Employee administration access
- **Applicant**: Job Vacancies Access
- **HRD**: Employee Management


## Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Bootstrap 5, Blade Templates
- **Database**: SQLite (development)
- **CSS Framework**: Bootstrap
- **Icons**: Bootstrap Icons
- **JavaScript**: Vanilla JS

## Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- SQLite

## Installation

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

   App will run on: `http://localhost:8000`
