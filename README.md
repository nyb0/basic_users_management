# Basic Users Management

A Laravel 12 application for user management with role-based access control, built with Inertia.js and Vue 3.

## Features

- **User Management**: Create, edit, delete, and search users with role-based permissions
- **Role System**: Three user roles - Admin, Moderator, and User
- **Authentication**: Login, registration, password reset, and email verification
- **Profile Management**: Users can update their profile and delete their account
- **FAQ System**: Public FAQ page with admin management (CRUD operations)
- **Site Settings**: Admin-only settings for About Us page and authentication configuration
- **Dashboard**: Role-specific dashboard with statistics

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.2)
- **Frontend**: Vue 3 + Inertia.js v2
- **Styling**: Tailwind CSS v3
- **Database**: MySQL 8.0
- **Cache/Queue/Session**: Redis
- **Containerization**: Docker

## Prerequisites

- Docker and Docker Compose
- Git

## Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/nyb0/basic_users_management.git
cd basic_users_management
```

### 2. Environment Configuration

The project includes a pre-configured `.env.docker` file for Docker deployment. Copy it to `.env`:

```bash
cp .env.docker .env
```

> **Note**: The `.env.docker` file contains a pre-generated `APP_KEY`. For production deployments, generate a new one with `php artisan key:generate`.

### 3. Build and Start Containers

```bash
docker compose up -d --build
```

This command:
- Builds the PHP-FPM container with Nginx
- Starts MySQL and Redis containers
- Runs database migrations automatically
- Starts phpMyAdmin (optional, for database management)

### 4. Create Default Admin User

After the containers are running, create the default admin user by running the database seeder:

```bash
docker compose exec app php artisan db:seed
```

### 5. Access the Application

- **Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

## Default Admin Credentials

After running `db:seed`, you can log in with:

| Field     | Value            |
|-----------|------------------|
| Email     | `admin_def@mail` |
| Password  | `1234QWER`       |

> **Important**: Change these credentials immediately after first login in a production environment!

## User Roles & Permissions

| Role      | Permissions |
|-----------|-------------|
| **Admin** | Full access: manage all users, FAQs, site settings |
| **Moderator** | Can manage users (cannot create/edit admins and moderators), view dashboard |
| **User** | Can view dashboard, edit own profile |

## Docker Services

| Service | Port | Description |
|---------|------|-------------|
| `app` | 8080 | Laravel application (PHP-FPM + Nginx) |
| `mysql` | 3307 | MySQL 8.0 database |
| `redis` | 6379 | Redis for cache, queues, sessions |
| `phpmyadmin` | 8081 | Database management UI |

## Common Commands

### Development

```bash
# Start all services
docker compose up -d

# View logs
docker compose logs -f app

# Stop all services
docker compose down

# Stop and remove volumes (clean slate)
docker compose down -v
```

### Artisan Commands

```bash
# Run migrations
docker compose exec app php artisan migrate

# Run migrations fresh (reset database)
docker compose exec app php artisan migrate:fresh --seed

# Clear cache
docker compose exec app php artisan cache:clear

# Run seeders
docker compose exec app php artisan db:seed

# Run specific seeder
docker compose exec app php artisan db:seed --class=AdminSeeder
```

### Frontend Development

```bash
# Install dependencies (if needed)
docker compose exec app npm install

# Build assets
docker compose exec app npm run build

# Development mode with hot reload (run locally, not in container)
npm run dev
```

## Docker Build Troubleshooting

### Build Context Issues

The `.dockerignore` file excludes unnecessary files from the Docker build context. If you encounter build issues:

1. **Ensure `.env.docker` exists** - It's required for the build process
2. **Check file permissions** - Storage and bootstrap/cache directories need write permissions
3. **Clear Docker cache** - Run `docker compose build --no-cache` to rebuild from scratch

### Common Build Errors

**Error: `no space left on device`**
```bash
# Prune unused Docker resources
docker system prune -a
```

**Error: `composer install` fails**
```bash
# Ensure composer.json and composer.lock are present
# Try building without cache
docker compose build --no-cache app
```

**Error: `npm run build` fails**
```bash
# Check Node version compatibility
# The container uses Node.js from Alpine repos
docker compose build --no-cache app
```

### Container Health Issues

If containers fail health checks:

```bash
# Check container status
docker compose ps

# Check MySQL connectivity
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();

# Check Redis connectivity
docker compose exec app redis-cli -h redis ping
```

## Local Development (Without Docker)

1. Install PHP 8.2, Composer, Node.js, MySQL, and Redis
2. Copy `.env.example` to `.env` and configure
3. Run:

```bash
composer install
npm install
npm run build
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Testing

```bash
# Run all tests
docker compose exec app php artisan test

# Run specific test file
docker compose exec app php artisan test --filter=AuthenticationTest
```

## Production Deployment

For production deployments:

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
2. Generate a new `APP_KEY`
3. Configure proper mail settings
4. Use HTTPS and set `APP_URL` accordingly
5. Run optimizations:

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).