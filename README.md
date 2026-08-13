# Kitchen Equipment Management

A technical exercise application for Duke Software Development Center (DSDC).

Users manage **Sites**, **Equipment**, and **Equipment ↔ Site registrations**, with role-based access for **SuperAdmin** and **Admin**.

## Technology Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.5, Laravel 13, Eloquent, Form Requests, Policies, Pest |
| Frontend | Vue 3, Inertia.js v3, PrimeVue 4 (MIT), Tailwind CSS v4, TypeScript |
| Database | MySQL/MariaDB (app), SQLite in-memory (tests) |
| Tooling | Vite, Wayfinder, Laravel Pint |

## Requirements

- PHP 8.3+ (8.5 recommended)
- Composer
- Node.js 20+ and npm
- MySQL 8+ or MariaDB (application)
- Docker optional (recommended for a local MySQL instance)

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Start MySQL (Docker recommended)

```bash
docker compose up -d
```

Default `.env` credentials match `docker-compose.yml`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=duke_kitchen
DB_USERNAME=duke
DB_PASSWORD=secret
```

If you use an existing MySQL server, create the `duke_kitchen` database and update `.env` accordingly.

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

Then open `http://localhost:8000`.

### Testing database

Pest/PHPUnit always uses **SQLite in-memory**, configured in `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

You do not need MySQL running to execute tests.

### Development with hot reload

```bash
composer run dev
```

## Demo Credentials

| Role | Username | Email | Password |
| --- | --- | --- | --- |
| SuperAdmin | `superadmin` | `superadmin@duke-kitchen.test` | `Password1!` |
| Admin 1 | `admin1` | `admin1@duke-kitchen.test` | `Password1!` |
| Admin 2 | `admin2` | `admin2@duke-kitchen.test` | `Password1!` |

Seeded highlights:

- Sites: KFC Angeles, Jollibee Marquee, SM Clark, DSDC Demo Kitchen
- Equipment: REF-001, REF-002, BLD-001, OVN-001, CHL-001 (unregistered), STV-001, FRZ-001

## Architecture

```
USER 1──N SITE
USER 1──N EQUIPMENT
SITE 1──N REGISTERED_EQUIPMENT
EQUIPMENT 1──1 REGISTERED_EQUIPMENT   (UNIQUE equipment_id)
```

### MVC responsibilities

- **Models** — relationships, casts, ownership scopes
- **Controllers** — thin HTTP orchestration + authorization
- **Form Requests** — validation
- **Policies** — SuperAdmin / ownership checks
- **Services** — multi-record business operations (`SiteService`, `EquipmentService`, `UserService`)
- **Vue / Inertia / PrimeVue** — presentation only

### Authentication

- Login accepts **username or email**
- Signup always creates **Admin** (never SuperAdmin)
- Passwords are hashed via Laravel’s `hashed` cast

### Authorization

| Capability | SuperAdmin | Admin |
| --- | --- | --- |
| User Maintenance | Yes | No (403) |
| Own Sites / Equipment | Yes | Yes |
| Other users’ Sites / Equipment | Yes | No (403) |

Navigation hides Users for Admins, but Policies enforce the same rules on the backend.

## Business Rules / Design Decisions

1. **`users` table naming** — Laravel convention (`users`) is used instead of literal `user`. Business columns are preserved: `user_type`, `first_name`, `last_name`, `email_address`, `user_name`, `password`.

2. **`registered_equipment` is required** — Site assignment lives in this association table, not an `equipment.site_id` column.

3. **One Site per Equipment** — enforced with `UNIQUE(registered_equipment.equipment_id)`.

4. **No separate Assign/Move workflow** — changing an Equipment’s Site is done in **Edit Equipment**. Site Equipment Maintenance is a read-only view of current registrations.

5. **Site optional on create** — Equipment may be created without a Site (demonstrated by seeded `CHL-001`). After Site deletion, Equipment remains unregistered.

6. **Delete Site** — in a transaction: delete that Site’s `registered_equipment` rows, then delete the Site. Equipment records are **not** deleted.

7. **Delete Equipment** — in a transaction: remove registration (if any), then delete Equipment.

8. **Ownership alignment** — Equipment and Site must belong to the same user. Admins cannot register to another user’s Site. When a SuperAdmin creates Equipment against a Site, ownership follows the Site owner so registration remains valid.

9. **Delete User** — blocked for the currently authenticated account. Also blocked if the user still owns Sites or Equipment (no silent cascade wipe).

10. **Admin cannot forge ownership** — `user_id` on Site create is ignored for Admins; ownership is taken from `auth()->id()`.

## Testing

```bash
php artisan test
```

Tests run against SQLite (`:memory:`) regardless of your local `.env` MySQL settings.

Coverage includes authentication, authorization/IDOR, site deletion unregistering, equipment registration uniqueness, and user deletion safeguards.

## Useful Commands

```bash
php artisan migrate:fresh --seed
php artisan test
npm run build
vendor/bin/pint --dirty
```

## Project Structure (high level)

```
app/
  Enums/
  Http/Controllers/
  Http/Requests/
  Models/
  Policies/
  Services/
resources/js/
  layouts/AppLayout.vue
  pages/Auth|Dashboard|Users|Sites|Equipment/
database/
  migrations/
  seeders/DemoSeeder.php
tests/Feature/
```

## Demo Flow (15–30 min)

1. Login as SuperAdmin → Dashboard → Users → edit a user
2. Logout → Login as Admin 1 → confirm Users is unavailable / 403
3. Sites → create/edit → Site Equipment view
4. Equipment → create with Site → edit and change Site
5. Delete a Site → confirm Equipment remains, now unregistered
6. Attempt cross-user Site/Equipment access → authorization failure
