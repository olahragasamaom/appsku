# 📋 Setup Instructions

Panduan step-by-step setup boilerplate.

## Prerequisites

- PHP 8.3+
- Composer
- Node.js & npm
- MySQL 8.0+
- Git

## Step 1: Create Fresh Laravel Project

```bash
laravel new my-app
cd my-app
```

## Step 2: Copy Boilerplate Files

Copy semua file dari boilerplate folder:

### Database Migrations (copy ke `database/migrations/`)
```
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
2026_02_11_030008_create_permission_tables.php
2026_08_11_020925_create_panritta_modules_table.php
```

### Database Seeders (copy ke `database/seeders/`)
```
RolePermissionSeeder.php
ModuleSeeder.php
```

Buat file `database/seeders/DatabaseSeeder.php`:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ModuleSeeder::class,
        ]);
    }
}
```

### Models (copy ke `app/Models/`)
```
User.php
Module.php
```

### Controllers (copy ke `app/Http/Controllers/`)
```
Auth/AuthenticatedSessionController.php
Auth/RegisteredUserController.php
Auth/PasswordResetController.php
DashboardController.php
```

### Views (copy ke `resources/views/`)
```
layouts/admin.blade.php
layouts/guest.blade.php
auth/login.blade.php
auth/register.blade.php
dashboard.blade.php
components/alert.blade.php
components/badge.blade.php
components/confirm-dialog.blade.php
components/table.blade.php
components/toast.blade.php
```

### Routes (copy ke `routes/`)
```
web.php
```

### Config Files (copy ke project root)
```
.env.example (rename to .env)
vite.config.js
package.json
composer.json
config/permission.php
```

## Step 3: Install Dependencies

```bash
composer install
npm install
php artisan key:generate
```

## Step 4: Setup Database

Edit `.env` file:
```env
DB_DATABASE=my_app
DB_USERNAME=root
DB_PASSWORD=
```

Create database:
```bash
mysql -u root -e "CREATE DATABASE my_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Run migrations:
```bash
php artisan migrate
```

## Step 5: Clear Cache & Seed

```bash
php artisan config:clear
php artisan cache:clear
php artisan db:seed
```

## Step 6: Build Assets

```bash
npm run build
```

## Step 7: Start Development Server

Terminal 1 - PHP Server:
```bash
php artisan serve
```

Terminal 2 - Vite Dev Server:
```bash
npm run dev
```

## Step 8: Access Application

Open browser:
```
http://localhost:8000
```

You will be redirected to login page.

## Login Credentials

After seeding, use these credentials:

```
Email: admin@example.com
Password: password
```

## 🎯 What You Get

After login, you'll see:

1. **Dashboard** - Simple welcome page
2. **Login/Register** - Authentication pages
3. **Roles & Permissions** - Already setup with Spatie
4. **Modules** - Ready to extend

## 🛠️ Troubleshooting

### "config/permission.php not loaded"
```bash
php artisan config:clear
php artisan cache:clear
php artisan migrate
```

### "SQLSTATE - Unknown database"
```bash
# Make sure database exists
mysql -u root -e "CREATE DATABASE my_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### "No such file or directory"
- Double-check file paths
- Ensure all files copied correctly
- Check file permissions

### "Class not found"
```bash
composer dump-autoload
php artisan config:clear
```

### Assets not loading (404)
```bash
npm run build
php artisan view:clear
```

## ✅ Success Checklist

After setup, verify:

- [ ] Database created successfully
- [ ] All migrations run (php artisan migrate:status)
- [ ] Can access login page (http://localhost:8000)
- [ ] Can login with credentials above
- [ ] Dashboard displays correctly
- [ ] No console errors (F12)
- [ ] CSS/JS loaded (not 404)

## 📝 Next Steps

### 1. Understand the Boilerplate
- **routes/web.php** - All application routes
- **app/Models/User.php** - User model with roles/permissions
- **app/Models/Module.php** - Module system
- **resources/views/dashboard.blade.php** - Dashboard page
- **app/Http/Controllers/Settings/UserController.php** - User management

### 2. Add New Module/Feature

Example: Add "Reports" module with CRUD

**Step 1: Create Controller**
```bash
php artisan make:controller ReportController
```

**Step 2: Add Routes** (in routes/web.php)
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::resource('reports', ReportController::class);
});
```

**Step 3: Create Views**
- `resources/views/reports/index.blade.php`
- `resources/views/reports/create.blade.php`
- `resources/views/reports/edit.blade.php`

**Step 4: Add Module** (in database/seeders/ModuleSeeder.php)
```php
Module::create([
    'name' => 'Reports',
    'slug' => 'reports',
    'icon' => 'chart',
    'is_active' => true,
]);
```

**Step 5: Run Seeder**
```bash
php artisan db:seed --class=ModuleSeeder
```

### 3. Assign Roles & Permissions

In seeder or manually:
```bash
php artisan tinker
# $admin = Role::find(1);
# $admin->givePermissionTo('view reports', 'create reports', 'edit reports', 'delete reports');
```

## 🚀 Deploy

For production:

```bash
# Build assets
npm run build

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set environment
APP_ENV=production
APP_DEBUG=false

# Run migrations on server
php artisan migrate --force
```

---

**Setup complete! Happy coding! 🎉**
