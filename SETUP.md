# Digital Marketplace - Setup & Development Guide

## Quick Start

### Start the Application
```bash
# Start all containers
docker compose up -d

# Check container status
docker compose ps

# View logs
docker compose logs -f php
docker compose logs -f nginx
```

### Stop the Application
```bash
# Stop all containers
docker compose down

# Stop without removing volumes (data persists)
docker compose stop

# Remove everything including volumes (WARNING: data loss)
docker compose down -v
```

---

## Database Management

### Access MySQL
```bash
# Interactive MySQL shell
docker compose exec mysql mysql -uroot -proot marketplace_dev

# Run a specific query
docker compose exec mysql mysql -uroot -proot marketplace_dev -e "SELECT * FROM users;"

# Backup database
docker compose exec mysql mysqldump -uroot -proot marketplace_dev > backup.sql

# Restore database
docker compose exec mysql mysql -uroot -proot marketplace_dev < backup.sql
```

### Run Migrations
```bash
# Run all pending migrations
docker compose exec php php artisan migrate

# Run migrations in test database
docker compose exec php php artisan migrate --database=mysql_test

# Rollback last migration
docker compose exec php php artisan migrate:rollback

# Reset database (drop all tables)
docker compose exec php php artisan migrate:reset

# Refresh database (reset + migrate)
docker compose exec php php artisan migrate:refresh

# Refresh with seeding
docker compose exec php php artisan migrate:refresh --seed
```

### Seed Database
```bash
# Run all seeders
docker compose exec php php artisan db:seed

# Run specific seeder
docker compose exec php php artisan db:seed --class=RoleSeeder

# Seed on migrate:fresh
docker compose exec php php artisan migrate:fresh --seed
```

---

## Laravel Artisan Commands

### Useful Commands
```bash
# Create new model with migration
docker compose exec php php artisan make:model Product -m

# Create controller
docker compose exec php php artisan make:controller ProductController --resource

# Create seeder
docker compose exec php php artisan make:seeder ProductSeeder

# Create migration
docker compose exec php php artisan make:migration create_products_table

# List all routes
docker compose exec php php artisan route:list

# Clear caches
docker compose exec php php artisan cache:clear
docker compose exec php php artisan config:clear
docker compose exec php php artisan view:clear

# Check application status
docker compose exec php php artisan about

# Generate application key
docker compose exec php php artisan key:generate
```

---

## Frontend Development

### Build Assets
```bash
# Development build (watch for changes)
npm run dev

# Production build (minified)
npm run build

# Build with Vite server
npm run dev
```

### Frontend File Structure
```
resources/
├── js/
│   ├── app.js (Entry point)
│   ├── bootstrap.js (Axios config)
│   ├── Pages/ (Inertia page components)
│   │   ├── Welcome.vue
│   │   ├── Dashboard.vue
│   │   └── Auth/
│   │       ├── Login.vue
│   │       ├── Register.vue
│   │       └── ...
│   ├── Components/ (Reusable Vue components)
│   └── Layouts/
│       ├── AuthenticatedLayout.vue
│       └── GuestLayout.vue
├── css/
│   └── app.css (Tailwind + custom styles)
└── views/
    └── app.blade.php (Inertia root template)
```

---

## Testing

### Run Tests
```bash
# Run all tests
docker compose exec php php artisan test

# Run specific test file
docker compose exec php php artisan test tests/Feature/AuthTest.php

# Run with coverage
docker compose exec php php artisan test --coverage
```

### Create Tests
```bash
# Feature test
docker compose exec php php artisan make:test UserAuthTest

# Unit test
docker compose exec php php artisan make:test ModelTest --unit
```

---

## Troubleshooting

### Container Won't Start
```bash
# Check logs
docker compose logs <service_name>

# Example for PHP container
docker compose logs php

# Rebuild container
docker compose build --no-cache php
docker compose up -d php
```

### Database Connection Issues
```bash
# Check MySQL is running and healthy
docker compose exec mysql mysqladmin -uroot -proot ping

# Check PHP can connect to MySQL
docker compose exec php php -r "
try {
    \$pdo = new PDO('mysql:host=mysql:3306;dbname=marketplace_dev', 'root', 'root');
    echo 'MySQL connection successful';
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage();
}
"
```

### Nginx Issues
```bash
# Check Nginx config
docker compose exec nginx nginx -t

# View Nginx logs
docker compose logs nginx

# Check if port 80 is in use
lsof -i :80

# Restart Nginx
docker compose restart nginx
```

### PHP-FPM Issues
```bash
# Check FPM status
docker compose exec php php-fpm -v

# View FPM logs
docker compose logs php | grep -i error

# Restart PHP
docker compose restart php
```

### Permission Issues
```bash
# Fix storage directory permissions
docker compose exec php chown -R www-data:www-data /app/storage
docker compose exec php chmod -R 775 /app/storage

# Fix bootstrap/cache permissions
docker compose exec php chown -R www-data:www-data /app/bootstrap/cache
docker compose exec php chmod -R 775 /app/bootstrap/cache
```

### Cache/Session Issues
```bash
# Clear all caches
docker compose exec php php artisan cache:clear

# Clear config cache
docker compose exec php php artisan config:clear

# Clear session cache
docker compose exec php php artisan session:clear

# Flush database cache table
docker compose exec php php artisan cache:flush
```

---

## Development Workflow

### Creating a New Feature

1. **Create Model & Migration**
   ```bash
   docker compose exec php php artisan make:model Category -m
   ```

2. **Define Migration**
   - Edit the migration file in `database/migrations/`
   - Define table structure

3. **Run Migration**
   ```bash
   docker compose exec php php artisan migrate
   ```

4. **Create Model Relationships**
   - Edit the model in `app/Models/`
   - Add relationships (hasMany, belongsTo, etc.)

5. **Create Controller**
   ```bash
   docker compose exec php php artisan make:controller CategoryController --resource
   ```

6. **Create Routes**
   - Edit `routes/web.php`
   - Add routes with middleware if needed

7. **Create Views (Inertia Components)**
   - Create Vue components in `resources/js/Pages/`
   - Use Inertia to pass data from controller

8. **Build Frontend**
   ```bash
   npm run build
   ```

### Testing Workflow

1. Write feature test
2. Run test to see it fail
3. Implement feature
4. Run test again to verify
5. Refactor if needed

---

## Environment Configuration

### .env Variables Reference

```
# Application
APP_NAME="Digital Marketplace"
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:... (auto-generated)
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=marketplace_dev
DB_USERNAME=root
DB_PASSWORD=root

# Cache & Sessions
CACHE_STORE=database
SESSION_DRIVER=database

# Redis (if using)
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null

# Mail
MAIL_MAILER=log
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS=noreply@marketplace.local

# NowPayments
NOWPAYMENTS_API_KEY=your_key
NOWPAYMENTS_IPN_SECRET=your_secret
NOWPAYMENTS_SANDBOX=true
```

### For Production

```
APP_ENV=production
APP_DEBUG=false
CACHE_STORE=redis
SESSION_DRIVER=redis
```

---

## Git Workflow

### Initialize Git
```bash
git init
git add .
git commit -m "Initial commit: Laravel + Breeze + Spatie setup"
```

### Create Feature Branch
```bash
git checkout -b feature/phase-3-models
```

### Commit Changes
```bash
git add .
git commit -m "Phase 3: Add product and category models"
```

### Merge to Main
```bash
git checkout main
git merge feature/phase-3-models
```

---

## Deployment Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate strong `APP_KEY`
- [ ] Configure `APP_URL` to production domain
- [ ] Setup SSL certificate
- [ ] Configure database backups
- [ ] Setup email service (not log driver)
- [ ] Configure NowPayments production keys
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Optimize: `php artisan optimize`
- [ ] Configure Laravel logging to external service
- [ ] Setup monitoring and alerting

---

## Common Tasks

### Add New User Role
```php
// In controller or seeder
\Spatie\Permission\Models\Role::create(['name' => 'moderator', 'guard_name' => 'web']);

// Assign to user
$user->assignRole('moderator');

// Check role
$user->hasRole('moderator');
```

### Protect Route with Role Middleware
```php
// In routes/web.php
Route::middleware('role:admin')->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});

// Or in controller
public function __construct() {
    $this->middleware('role:seller');
}
```

### Access Authenticated User
```php
// In controller
$user = auth()->user(); // Get logged-in user
$user->roles; // Get user roles
$user->hasRole('seller'); // Check role

// In Blade/Vue
auth()->user() // In PHP
$page.props.auth.user // In Vue components
```

### Database Query Examples
```bash
# Count users by role
docker compose exec php php artisan tinker
>>> User::with('roles')->get()
>>> User::role('seller')->count()
```

---

## Useful Links

- [Laravel Documentation](https://laravel.com/docs)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)
- [Vue 3 Documentation](https://vuejs.org/)
- [Tailwind CSS](https://tailwindcss.com/)

---

## Support

For issues or questions:
1. Check PROGRESS.log for previous solutions
2. Review PROJECT_STATUS.md for current phase
3. Check container logs: `docker compose logs <service>`
4. Verify environment variables in .env
5. Check Laravel logs: `tail -f storage/logs/laravel.log`
