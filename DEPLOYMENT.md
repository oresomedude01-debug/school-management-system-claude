# School Management System - cPanel Deployment Guide

This Laravel application is designed to be deployed on cPanel hosting **WITHOUT requiring npm install** or any build process. All frontend assets are pure JavaScript and CSS - no build tools needed!

## Features
- ✅ Laravel 12 Backend
- ✅ Pure Vanilla JavaScript (React-like components)
- ✅ Pure CSS (No preprocessors)
- ✅ No npm dependencies for frontend
- ✅ Direct cPanel deployment
- ✅ Complete School Management System

## Prerequisites
- cPanel hosting account with:
  - PHP 8.2 or higher
  - MySQL/MariaDB database
  - Composer installed
  - SSH access (recommended)

## Deployment Steps

### 1. Prepare Your cPanel Account

#### Create a Database
1. Log in to cPanel
2. Go to **MySQL Databases**
3. Create a new database (e.g., `schooldb`)
4. Create a database user with a strong password
5. Add the user to the database with ALL PRIVILEGES
6. Note down:
   - Database name
   - Database user
   - Database password
   - Database host (usually `localhost`)

### 2. Upload Files

#### Option A: Using Git (Recommended)
```bash
# SSH into your cPanel account
cd public_html

# Clone your repository
git clone <your-repo-url> school-app
cd school-app
```

#### Option B: Using File Manager
1. Zip your entire project folder
2. Upload via cPanel File Manager
3. Extract to `public_html/school-app`

### 3. Configure Environment

1. Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

2. Edit `.env` file with your database credentials:
```env
APP_NAME="School Management System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### 4. Install Dependencies & Setup

```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate --force

# Clear and cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Set Up Document Root

#### Method 1: Using .htaccess (If you can't change document root)

Create `.htaccess` in your `public_html` folder:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ school-app/public/$1 [L]
</IfModule>
```

#### Method 2: Change Document Root (Preferred)

1. In cPanel, go to **Domains**
2. Find your domain
3. Click **Manage**
4. Change **Document Root** to: `public_html/school-app/public`
5. Save changes

### 6. Set Permissions

```bash
# Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R your_cpanel_user:your_cpanel_user storage bootstrap/cache

# If using SSH
cd /home/your_cpanel_user/public_html/school-app
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 7. Create Default Admin User

Create a database seeder or use Tinker:

```bash
php artisan tinker
```

Then run:
```php
$user = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@school.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin',
    'phone' => '1234567890',
    'address' => 'Admin Address'
]);
```

### 8. Verify Installation

Visit your domain: `https://yourdomain.com`

You should see the login page. Login with:
- Email: `admin@school.com`
- Password: `admin123`

## Project Structure

```
school-app/
├── app/
│   ├── Http/Controllers/Api/    # All API controllers
│   └── Models/                  # Eloquent models
├── database/
│   └── migrations/              # Database schema
├── public/
│   ├── css/
│   │   └── app.css             # Pure CSS (no build needed)
│   └── js/
│       ├── framework.js        # React-like framework
│       ├── components.js       # UI components
│       └── app.js              # Main application
├── resources/
│   └── views/
│       └── app.blade.php       # Main HTML template
└── routes/
    └── web.php                 # All routes (API + Web)
```

## No Build Tools Required!

Unlike traditional Laravel + React apps, this project:
- ✅ No `npm install` needed
- ✅ No `npm run build` required
- ✅ No webpack, vite, or any bundler
- ✅ Pure JavaScript ES6+ (works in all modern browsers)
- ✅ Pure CSS (no SASS/LESS compilation)
- ✅ Direct deploy to cPanel
- ✅ Just upload and run!

## Troubleshooting

### Issue: 500 Internal Server Error
**Solution:**
1. Check `.htaccess` in `public` folder exists
2. Verify file permissions (755 for directories, 644 for files)
3. Check error logs in cPanel → Error Log
4. Ensure PHP version is 8.2+

### Issue: Database Connection Failed
**Solution:**
1. Verify database credentials in `.env`
2. Ensure database user has privileges
3. Try `localhost` or `127.0.0.1` for DB_HOST

### Issue: Blank Page After Login
**Solution:**
1. Clear browser cache
2. Check browser console for JavaScript errors
3. Verify all JS files are accessible (check 404 errors)

### Issue: CSS Not Loading
**Solution:**
1. Check if `public/css/app.css` exists
2. Verify correct document root
3. Clear Laravel cache: `php artisan cache:clear`

## Updating the Application

```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Run new migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Re-cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Security Recommendations

1. **Change default admin password immediately**
2. **Set `APP_DEBUG=false` in production**
3. **Use strong database passwords**
4. **Keep Laravel updated**: `composer update`
5. **Enable SSL certificate** (Let's Encrypt via cPanel)
6. **Set proper file permissions**
7. **Regular backups** of database and files

## Features Included

### Backend (Laravel)
- User authentication (session-based)
- Student management (CRUD)
- Teacher management (CRUD)
- Class management
- Subject management
- Attendance tracking
- Grade/Results management
- Dashboard with statistics
- RESTful API endpoints

### Frontend (Vanilla JavaScript)
- React-like component system
- Client-side routing
- State management
- Form handling
- AJAX requests
- Modern responsive UI
- Dashboard with stats
- Data tables
- Form validation

## Performance Optimization

Already included:
- ✅ Autoloader optimization
- ✅ Config caching
- ✅ Route caching
- ✅ View caching
- ✅ Minified architecture
- ✅ Efficient database queries with Eloquent
- ✅ Lazy loading of relationships

## Browser Support

Works on all modern browsers:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Support & Maintenance

### Database Backup
Use cPanel phpMyAdmin or command line:
```bash
mysqldump -u username -p database_name > backup.sql
```

### Log Files
Monitor application logs:
- Laravel logs: `storage/logs/laravel.log`
- cPanel Error logs: Access via cPanel

## Conclusion

Your Laravel School Management System is now deployed on cPanel without any npm dependencies! The entire frontend is built with pure JavaScript and CSS, making deployment and maintenance incredibly simple.

For questions or issues, check the Laravel documentation or refer to this guide.

Happy School Managing! 🎓
