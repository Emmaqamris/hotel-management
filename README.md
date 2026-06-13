# 🏨 Hotel Management System

A complete, production-ready Hotel Management System built with Laravel 11, MySQL, React, and Tailwind CSS.

## ✨ Features

- 🏠 Room Management (CRUD, status, images)
- 📅 Booking System (double-booking prevention)
- 👥 Guest Management (profiles, history, search)
- 🛎️ Front Desk (check-in/out, room board)
- 💳 Invoice & Payment System
- 📊 Dashboard & Analytics (React charts)
- 📈 Reports (Revenue, Occupancy, Bookings)
- 🧹 Housekeeping Management
- 👨‍💼 Employee Management (role-based access)
- 📧 Email Notifications (queue jobs)
- 🔌 REST API (Laravel Sanctum)

## 🛠️ Tech Stack

- **Backend:** Laravel 11 (PHP 8.3)
- **Database:** MySQL
- **Frontend:** Blade + Tailwind CSS + React
- **Charts:** Recharts
- **Auth:** Laravel Sanctum
- **Queue:** Database/Redis
- **Email:** Gmail SMTP

## ⚙️ Installation

### Requirements
- PHP 8.3+
- MySQL 8.0+
- Node.js 18+
- Composer

### Setup

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/hotel-management.git
cd hotel-management

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Environment setup
cp .env.example .env
php artisan key:generate

# 5. Configure your .env file
# Set DB_DATABASE, DB_USERNAME, DB_PASSWORD
# Set MAIL_* settings

# 6. Run migrations and seed demo data
php artisan migrate --seed

# 7. Build frontend assets
npm run build

# 8. Link storage
php artisan storage:link

# 9. Start the server
php artisan serve
```

## 🔑 Demo Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@hotel.com | password |
| Manager | manager@hotel.com | password |
| Receptionist | reception@hotel.com | password |
| Housekeeper | housekeeper@hotel.com | password |

## 👥 Role Access

| Feature | Admin | Manager | Receptionist | Housekeeper |
|---------|-------|---------|--------------|-------------|
| Dashboard & Reports | ✅ | ✅ | ❌ | ❌ |
| Bookings | ✅ | ✅ | ✅ | ❌ |
| Rooms | ✅ | ✅ | ✅ | ❌ |
| Guests | ✅ | ✅ | ✅ | ❌ |
| Invoices & Payments | ✅ | ✅ | ✅ | ❌ |
| Housekeeping | ✅ | ✅ | ❌ | ✅ |
| Employees | ✅ | ✅ | ❌ | ❌ |

## 🧪 Testing

```bash
php artisan test
# 125 tests, 298 assertions — all passing ✅
```

## 📡 API

Base URL: `/api`
Auth: Bearer token (Sanctum)

```bash
# Login
POST /api/auth/login

# Rooms
GET  /api/rooms
GET  /api/rooms/available

# Bookings
GET  /api/bookings
POST /api/bookings
POST /api/bookings/{id}/check-in
POST /api/bookings/{id}/check-out

# Dashboard
GET  /api/dashboard
GET  /api/reports/revenue
GET  /api/reports/occupancy
```

## 📧 Email Notifications

- ✅ Booking confirmation
- ✅ Check-in reminder (day before)
- ✅ Cancellation notice
- ✅ Payment receipt

Start queue worker to process emails:
```bash
php artisan queue:work
```

## 🚀 Production Deployment

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

## 👨‍💻 Developer

**Emmanuel (QAMRIS)**
Mbeya University of Science and Technology

---
Built with ❤️ using Laravel 11