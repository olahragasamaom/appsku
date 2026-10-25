# 🚀 Minimal Laravel Boilerplate

Simple boilerplate dengan hanya **User** dan **Module** tables.

## ✨ Yang Disediakan

- ✅ User authentication (login, register, password reset)
- ✅ Admin dashboard (simple)
- ✅ Role & permission system (Spatie)
- ✅ Module management
- ✅ Beautiful UI dengan Tailwind CSS + Alpine.js

## 📦 Yang NOT Termasuk

- ❌ Multi-tenant / Company isolation
- ❌ Complex employee/payroll system
- ❌ Face recognition
- ❌ Detailed dashboard charts

## 🚀 Quick Setup (5 menit)

### 1. Copy Files ke Project Baru
```bash
# Buat Laravel project baru
laravel new my-app
cd my-app

# Copy semua folder dari boilerplate:
# - MIGRATION_FILES/* → database/migrations/
# - SEEDER_FILES/* → database/seeders/
# - APP_MODELS/* → app/Models/
# - APP_CONTROLLERS/* → app/Http/Controllers/
# - VIEWS/* → resources/views/
# - ROUTES/web.php → routes/web.php
# - CONFIG_FILES/* → project root
```

### 2. Install Dependencies
```bash
composer install
npm install
php artisan key:generate
```

### 3. Setup Database
```bash
# Edit .env dengan database credentials
# DB_DATABASE=my_app
# DB_USERNAME=root
# DB_PASSWORD=

php artisan migrate
php artisan config:clear
php artisan cache:clear
php artisan db:seed
```

### 4. Start Development
```bash
# Terminal 1
npm run dev

# Terminal 2
php artisan serve
```

## 🔐 Login

**Default credentials:**
```
Email: admin@example.com
Password: password
```

**Dashboard URL:**
```
http://localhost:8000/dashboard
```

## 📁 File Structure

```
boilerplate/
├── MIGRATION_FILES/           # Database migrations
├── SEEDER_FILES/              # Database seeders
├── APP_MODELS/                # Eloquent models (User, Module)
├── APP_CONTROLLERS/           # Controllers (Auth, Dashboard)
├── VIEWS/                     # Blade views
├── ROUTES/                    # Web routes
├── CONFIG_FILES/              # Config & dependencies
└── README.md                  # This file
```

## 🎯 Database Schema

### users table
```sql
- id (primary key)
- name
- email (unique)
- password
- email_verified_at
- remember_token
- timestamps
- soft_deletes
```

### modules table
```sql
- id (primary key)
- name
- slug
- description
- icon
- is_active
- timestamps
```

### permission tables (Spatie)
- roles
- permissions
- model_has_roles
- model_has_permissions
- role_has_permissions

## 🛣️ Routes

### Public Routes (Guest)
```php
GET    /login                   → Login page
POST   /login                   → Submit login
GET    /register                → Register page
POST   /register                → Submit register
GET    /forgot-password         → Forgot password page
POST   /forgot-password         → Send reset email
GET    /reset-password/{token}  → Reset password page
POST   /reset-password          → Submit password reset
```

### Protected Routes (Auth Required)
```php
GET    /                        → Redirect to dashboard
GET    /dashboard               → Dashboard home
POST   /logout                  → Logout

// User Management
GET    /settings/users          → List all users
GET    /settings/users/create   → Create user form
POST   /settings/users          → Store new user
GET    /settings/users/{id}/edit → Edit user form
PUT    /settings/users/{id}     → Update user
DELETE /settings/users/{id}     → Delete user
```

## 🎨 UI Components

Available components:
- `<x-alert />` - Alert notification
- `<x-badge />` - Status badge
- `<x-table>` - Data table
- `<x-confirm-dialog />` - Confirmation modal
- `<x-toast />` - Toast notification

## 🧪 Testing

Run tests:
```bash
php artisan test
```

## 📝 Customization

### Add New Role
Edit `RolePermissionSeeder.php`:
```php
$admin = Role::create(['name' => 'admin']);
$admin->givePermissionTo(['create', 'edit', 'delete']);
```

### Add New Module
Edit `ModuleSeeder.php`:
```php
Module::create([
    'name' => 'Reports',
    'slug' => 'reports',
]);
```

### Change Colors
Edit `resources/css/app.css` atau `tailwind.config.js`

## 🚀 Deploy

```bash
# Build assets for production
npm run build

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📚 Requirements

- PHP 8.3+
- Laravel 12
- MySQL 8.0+ / PostgreSQL 12+
- Node.js 18+

## 📄 License

MIT

---

**Simple, clean, and ready to extend!** 🎉
