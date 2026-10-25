# 🚀 Getting Started

Panduan cepat untuk menggunakan boilerplate ini.

## ✅ Pre-Setup Checklist

Pastikan installed:
- [ ] PHP 8.3+
- [ ] Composer
- [ ] Node.js & npm
- [ ] MySQL 8.0+
- [ ] Git (optional)

## 📋 Setup Steps

### Step 1: Create Laravel Project
```bash
laravel new my-app
cd my-app
```

### Step 2: Copy Boilerplate Files

**Option A: Manual Copy**
Copy these folders from boilerplate:
```
MIGRATION_FILES  → database/migrations/
SEEDER_FILES     → database/seeders/
APP_MODELS       → app/Models/
APP_CONTROLLERS  → app/Http/Controllers/
VIEWS            → resources/views/
ROUTES/web.php   → routes/web.php
CONFIG_FILES     → project root
```

**Option B: Automated (Windows PowerShell)**
```powershell
# Copy migrations
Copy-Item -Path "boilerplate\MIGRATION_FILES\*" -Destination "database\migrations\" -Force

# Copy seeders
Copy-Item -Path "boilerplate\SEEDER_FILES\*" -Destination "database\seeders\" -Force

# Copy models
Copy-Item -Path "boilerplate\APP_MODELS\*" -Destination "app\Models\" -Force

# Copy controllers
Copy-Item -Path "boilerplate\APP_CONTROLLERS\*" -Destination "app\Http\Controllers\" -Recurse -Force

# Copy views
Copy-Item -Path "boilerplate\VIEWS\*" -Destination "resources\views\" -Recurse -Force

# Copy routes
Copy-Item -Path "boilerplate\ROUTES\web.php" -Destination "routes\web.php" -Force

# Copy configs
Copy-Item -Path "boilerplate\CONFIG_FILES\*" -Destination ".\" -Force
```

### Step 3: Install Dependencies
```bash
composer install
npm install
php artisan key:generate
```

### Step 4: Setup Database

Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_app        # Create this database
DB_USERNAME=root
DB_PASSWORD=
```

Create database:
```bash
mysql -u root -e "CREATE DATABASE my_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 5: Run Migrations & Seeds

```bash
php artisan migrate

# Clear cache before seeding
php artisan config:clear
php artisan cache:clear

# Seed database
php artisan db:seed
```

### Step 6: Build Assets
```bash
npm run build
```

### Step 7: Start Development

**Terminal 1 - PHP Server:**
```bash
php artisan serve
```

**Terminal 2 - Vite (Assets):**
```bash
npm run dev
```

## 🔐 Login

Open browser: `http://localhost:8000`

You'll be redirected to login page.

**Default Credentials:**
```
Email: admin@example.com
Password: password
```

## 📍 What You Get

After login:
- ✅ Dashboard
- ✅ User Management (/settings/users)
- ✅ Roles & Permissions
- ✅ Modules System

## 🎯 Available Pages

| URL | Description | Auth Required |
|-----|-------------|---|
| `/login` | Login page | No |
| `/register` | Register page | No |
| `/forgot-password` | Forgot password | No |
| `/dashboard` | Dashboard home | Yes |
| `/settings/users` | User list | Yes |
| `/settings/users/create` | Create user | Yes |
| `/settings/users/{id}/edit` | Edit user | Yes |

## 🔧 Troubleshooting

### "Database connection refused"
```bash
# Check MySQL running
# Update DB_HOST in .env to 127.0.0.1 or localhost
# Make sure database exists: CREATE DATABASE my_app;
```

### "config/permission.php not loaded"
```bash
php artisan config:clear
php artisan cache:clear
php artisan migrate
```

### "Assets not loading (404)"
```bash
npm run build
php artisan view:clear
```

### "Class not found"
```bash
composer dump-autoload
php artisan config:clear
```

### "No such file or directory"
- Verify all files copied correctly
- Check folder structure matches boilerplate
- Ensure file paths are correct

## 💡 Next Steps

1. **Explore Dashboard** - See `/dashboard` after login
2. **Manage Users** - Go to `/settings/users`
3. **Understand Code:**
   - Check `routes/web.php` for routes
   - Check `app/Models/User.php` for User model
   - Check `app/Http/Controllers/DashboardController.php` for example controller

4. **Add New Features:**
   - Create controller: `php artisan make:controller`
   - Create model: `php artisan make:model`
   - Create migration: `php artisan make:migration`
   - Add routes in `routes/web.php`
   - Create views

## 📚 Documentation

- **README.md** - Overview & features
- **SETUP.md** - Detailed setup
- **GETTING_STARTED.md** - This file

## ✨ Features

- ✅ User authentication (login, register, password reset)
- ✅ Dashboard
- ✅ User management
- ✅ Roles & permissions (Spatie)
- ✅ Module system
- ✅ Beautiful UI (Tailwind CSS + Alpine.js)
- ✅ Responsive design
- ✅ Form components
- ✅ Confirmation dialogs
- ✅ Toast notifications

## 📦 Dependencies

- Laravel 12
- Spatie Laravel Permission
- Laravel Sanctum
- Tailwind CSS 4
- Alpine.js 3

## 🎉 You're Ready!

Start building with this clean, minimal boilerplate!

---

**Questions?** Check the documentation or explore the code. Happy coding! 🚀
