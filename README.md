# Kitchen Equipment Management

A technical exercise application for Duke Software Development Center (DSDC).

Users manage **Sites**, **Equipment**, and **Equipment ↔ Site registrations**, with role-based access for **SuperAdmin** and **Admin**.

## Technology Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.5, Laravel 13, Sanctum, Eloquent, Form Requests, Policies, Pest |
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
git clone git@github.com:heraldsuva/duke-kitchen.git
cd duke-kitchen

composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Start MySQL (Docker recommended)

Requires Docker Desktop (or equivalent) running locally.

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
- **Controllers** — thin HTTP orchestration + authorization (`App\Http\Controllers` for Inertia pages, `App\Http\Controllers\Api` for JSON)
- **Form Requests** — validation
- **Policies** — SuperAdmin / ownership checks
- **Services** — multi-record business operations (`SiteService`, `EquipmentService`, `UserService`)
- **Vue / Inertia / PrimeVue** — presentation only

### Authentication

Authentication uses **Laravel Sanctum personal access tokens**. The Vue/Inertia UI stores the token in `localStorage` and sends it as `Authorization: Bearer {token}` on Inertia page visits and API mutations.

- Login accepts **username or email** via `POST /api/auth/login`
- Signup always creates **Admin** (never SuperAdmin) via `POST /api/auth/signup`
- Logout revokes the current token via `POST /api/auth/logout`
- Protected Inertia pages (`GET /dashboard`, etc.) require a valid Bearer token
- Passwords are hashed via Laravel’s `hashed` cast

## API Reference (Sanctum tokens)

All JSON endpoints live under `/api`.

### OpenAPI specification

An OpenAPI 3.0 spec is provided as `openapi.yaml` at the project root. You can import it into tools like Swagger UI / Postman to browse endpoints and schemas.

### Base URL

- `http://localhost:8000/api/...` (adjust port/host as needed)

### Authentication

This project uses **Laravel Sanctum personal access tokens**.

- Token is issued by:
  - `POST /api/auth/login`
  - `POST /api/auth/signup`
- Token is sent on every protected API request:
  - Header: `Authorization: Bearer {token}`
- The Vue/Inertia UI stores the token in `localStorage` under `auth_token`.

### Request headers

- Always: `Accept: application/json`
- For protected routes: `Authorization: Bearer {token}`

### Response format

List endpoints return a wrapper:

```json
{ "data": [ /* items */ ] }
```

Create/update endpoints usually return:

```json
{ "message": "....", "data": { /* entity */ } }
```

Delete endpoints usually return:

```json
{ "message": "...." }
```

### Error format

Authentication / authorization:

- `401 Unauthorized`: missing or invalid token
- `403 Forbidden`: policy denied (same rules as the Inertia pages)

Validation:

- `422 Unprocessable Entity`: Laravel validation errors, typically shaped like:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Authentication

#### Login

`POST /api/auth/login`

```json
{
  "login": "admin1",
  "password": "Password1!"
}
```

**200 OK**

```json
{
  "token": "1|plainTextToken...",
  "token_type": "Bearer",
  "user": {
    "id": 2,
    "first_name": "Admin",
    "last_name": "One",
    "full_name": "Admin One",
    "user_name": "admin1",
    "email_address": "admin1@duke-kitchen.test",
    "user_type": "Admin",
    "is_super_admin": false
  }
}
```

**422** — invalid credentials (`errors.login`)

#### Signup

`POST /api/auth/signup`

```json
{
  "first_name": "Jane",
  "last_name": "Doe",
  "email_address": "jane@example.com",
  "user_name": "jane",
  "password": "Password1!",
  "password_confirmation": "Password1!"
}
```

**201 Created** — same shape as login response. `user_type` is always `Admin`.

#### Current user

`GET /api/auth/user` (authenticated)

**200 OK** — `{ "user": { ... } }`

#### Logout

`POST /api/auth/logout` (authenticated)

**200 OK** — `{ "message": "Logged out successfully." }`

### Users (SuperAdmin only)

| Method | Path | Description |
| --- | --- | --- |
| GET | `/api/users` | List users |
| POST | `/api/users` | Create user |
| PUT/PATCH | `/api/users/{id}` | Update user |
| DELETE | `/api/users/{id}` | Delete user |

Create/update body:

```json
{
  "first_name": "Dana",
  "last_name": "Lim",
  "email_address": "dana@example.com",
  "user_name": "dana",
  "password": "Password1!",
  "password_confirmation": "Password1!",
  "user_type": "Admin"
}
```

Successful mutations return `{ "message": "...", "data": { ... } }`. Delete returns `{ "message": "..." }`.

GET example response:

```json
{
  "data": [
    {
      "id": 2,
      "first_name": "Admin",
      "last_name": "One",
      "full_name": "Admin One",
      "user_name": "admin1",
      "email_address": "admin1@duke-kitchen.test",
      "user_type": "Admin",
      "sites_count": 0,
      "equipment_count": 0
    }
  ]
}
```

### Sites

| Method | Path | Description |
| --- | --- | --- |
| GET | `/api/sites` | List sites visible to the authenticated user |
| POST | `/api/sites` | Create site |
| PUT/PATCH | `/api/sites/{id}` | Update site |
| DELETE | `/api/sites/{id}` | Delete site (unregisters equipment) |

Create body:

```json
{
  "description": "KFC Angeles",
  "active": true,
  "user_id": 2
}
```

`user_id` is optional; SuperAdmin may assign ownership. Admins always own sites they create.

Update body (owner cannot be changed here):

```json
{
  "description": "Updated name",
  "active": false
}
```

GET example response:

```json
{
  "data": [
    {
      "id": 5,
      "description": "KFC Angeles",
      "active": true,
      "equipment_count": 3,
      "owner": {
        "id": 2,
        "name": "Admin One",
        "user_name": "admin1"
      }
    }
  ]
}
```

### Equipment

| Method | Path | Description |
| --- | --- | --- |
| GET | `/api/equipment` | List equipment visible to the authenticated user |
| POST | `/api/equipment` | Create equipment (optional site registration) |
| PUT/PATCH | `/api/equipment/{id}` | Update equipment and/or change site |
| DELETE | `/api/equipment/{id}` | Delete equipment and its registration |

Create/update body:

```json
{
  "serial_number": "REF-001",
  "description": "Walk-in refrigerator",
  "condition": "Working",
  "site_id": 1
}
```

`site_id` may be `null` to leave equipment unregistered.

GET example response:

```json
{
  "data": [
    {
      "id": 10,
      "serial_number": "REF-001",
      "description": "Walk-in refrigerator",
      "condition": "Working",
      "site_id": 5,
      "site": "KFC Angeles",
      "owner": "Admin One",
      "user_id": 2
    }
  ]
}
```

### HTTP status codes

| Code | Meaning |
| --- | --- |
| 200 | Success |
| 201 | Created |
| 401 | Missing or invalid token |
| 403 | Policy denied |
| 422 | Validation failed (`errors` object) |

### Example: curl login + list sites

```bash
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"login":"admin1","password":"Password1!"}' \
  | jq -r .token)

curl -s http://localhost:8000/api/sites \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | jq
```


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
  Http/Controllers/Api/
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
