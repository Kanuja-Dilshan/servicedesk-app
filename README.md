# ServiceDesk

IT support ticketing system for company employees. Employees report issues, IT Support investigates and resolves them, and Admins manage users and categories.

Built with **Laravel, MySQL, Blade + Tailwind CSS, Sanctum (REST API), Docker**.

## Features

- Role-based access: Employee, IT Support, Admin (Laravel Policies and Gates)
- Ticket workflow: Open → Assigned → In Progress → Waiting for User → Resolved → Closed
- Comments, internal staff-only notes, private file attachments
- Search, status filter, pagination
- In-app and email notifications (assignment, status change, comments)
- Dashboard with status/priority statistics and SLA overdue tracking
- Audit log: history of every status and assignee change
- Admin panel for users and ticket categories
- REST API with token authentication
- Automated feature tests

## Roles

| Role | Can do |
|------|--------|
| Employee | Create tickets, see own tickets, comment, upload files |
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
php artisan serve      # terminal 1
npm run dev            # terminal 2
```

Demo accounts (local only, password `password`): `admin@demo.test`, `tech@demo.test`, `emp@demo.test`

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

## Docker

```bash
cp .env.example .env.docker      # then set APP_ENV=production, APP_KEY, DB_HOST=db and strong passwords
docker compose up -d --build
docker compose exec app php artisan migrate --force --seed
docker compose exec app php artisan servicedesk:create-admin you@company.com "Your Name"
```

The app is served at http://localhost:8080. In production, put it behind HTTPS and never use the demo accounts.

## Project structure

- `app/Models/Ticket.php`: ticket model, visibility scope, audit-log events
- `app/Policies/TicketPolicy.php`: who can view / manage / comment
- `app/Http/Controllers/`: web controllers; `Api/` holds the REST controllers
- `app/Notifications/TicketActivity.php`: database + mail notification
- `database/seeders/`: lookup data (all environments) and demo users (local only)