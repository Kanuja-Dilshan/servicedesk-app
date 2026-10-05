# ServiceDesk

IT support ticketing system for company employees. Employees report issues, IT Support investigates and resolves them, and Admins manage users and categories.

Built with **Laravel, MySQL, Blade + Tailwind CSS, Sanctum (REST API)**.

## Features

- Role-based access: Employee, IT Support, Admin (Laravel Policies and Gates)
- Ticket workflow: Open → Assigned → In Progress → Waiting for User → Resolved → Closed
- Requester can confirm (close) or reopen a resolved ticket; unanswered resolved tickets auto-close after 7 days
- Comments, internal staff-only notes, private file attachments
- Search, status filter, pagination, status and priority badges
- Notifications: in-app bell plus email (sent in the background via a queue)
- Dashboard with status/priority statistics and SLA overdue tracking
- Audit log: history of every status and assignee change
- Admin panel for users and ticket categories
- REST API with token authentication
- Automated feature tests

## Roles

| Role | Can do |
|------|--------|
| Employee | Create tickets, see own tickets, comment, upload files, close or reopen own resolved tickets |
| IT Support | See all tickets, assign, change status, internal notes |
| Admin | Everything above, plus manage users and categories |

## Local setup

Requirements: PHP 8.3+, Composer, Node.js 20+, MySQL.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# set DB_* values in .env and create the database first
php artisan migrate --seed
php artisan storage:link
```

Run these in separate terminals:

```bash
php artisan serve                      # the app
npm run dev                            # frontend assets
php artisan queue:listen --tries=3     # sends emails in the background
php artisan schedule:work              # runs the daily auto-close (optional locally)
```

Demo accounts (local only, password `password`): `admin@demo.test`, `tech@demo.test`, `emp@demo.test`

For emails during development, use a sandbox such as Mailtrap and set `MAIL_*` in `.env`.

## Tests

```bash
php artisan test
```

Tests use an in-memory SQLite database (see `phpunit.xml`) and never touch the MySQL data.

## REST API

```
POST /api/login                     { "email", "password" }  → { token }
GET  /api/tickets                   ?q=search&status=3
POST /api/tickets                   { title, description, category_id, priority_id }
GET  /api/tickets/{id}
PUT  /api/tickets/{id}              { status_id, assigned_to }   (staff only)
POST /api/tickets/{id}/comments     { body, is_internal }
```

Send `Accept: application/json` and `Authorization: Bearer <token>` on every request after login.

## Production deployment (Linux + Nginx + MySQL)

1. Install PHP 8.3 (fpm, mysql, mbstring, xml, curl, zip), Composer, Nginx, MySQL, Git.
2. Clone the repo, then:
```bash
   composer install --no-dev --optimize-autoloader
   cp .env.example .env     # set APP_ENV=production, APP_DEBUG=false, APP_URL, DB_*, MAIL_*
   php artisan key:generate
   php artisan migrate --force --seed
   php artisan servicedesk:create-admin you@company.com "Your Name"
   php artisan storage:link
   php artisan optimize
```
   With `APP_ENV=production` the seeder only creates statuses, priorities and categories; demo accounts are never created.
3. Make `storage` and `bootstrap/cache` writable by the web user: `chown -R www-data:www-data storage bootstrap/cache`.
4. Nginx server block: point `root` at `public/`, use `try_files $uri $uri/ /index.php?$query_string;`, and pass `.php` to PHP-FPM. Enable HTTPS (for example with Certbot).
5. Keep the queue worker running with Supervisor: `php artisan queue:work --tries=3`.
6. Run the scheduler with one cron entry: `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`
7. Back up the database regularly (`mysqldump`) and the `storage/app` folder (attachments).

## Project structure

- `app/Models/Ticket.php`: ticket model, visibility scope, audit-log events
- `app/Policies/TicketPolicy.php`: who can view / manage / comment / respond
- `app/Http/Controllers/`: web controllers; `Api/` holds the REST controllers
- `app/Notifications/TicketActivity.php`: database + queued mail notification
- `app/Console/Commands/`: `servicedesk:create-admin`, `servicedesk:auto-close`
- `database/seeders/`: lookup data (all environments) and demo users (local only)