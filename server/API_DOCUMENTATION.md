# Bait El Kebash API Documentation

Reference for the React frontend and for anyone working on the database.
Everything here is derived from `routes/api.php`, the API controllers, the
`app/Http/Resources/*` classes and `database/migrations/*`. When any of those
change, update this file.

- [Conventions](#conventions)
- [Authentication](#authentication)
- [Endpoints](#endpoints)
- [Response objects](#response-objects)
- [Database schema](#database-schema)
- [Business rules](#business-rules)
- [Known issues](#known-issues)

---

## Conventions

### Base URL

```js
const API_URL = import.meta.env.VITE_API_URL || "http://localhost:8000/api";
```

All endpoints are relative to `API_URL`. Always send `Accept: application/json`,
otherwise Laravel may answer errors with HTML redirects instead of JSON.

### Response shapes

There are **three** shapes. Which one you get depends on the endpoint (listed per endpoint below).

1. **Single resource** (e.g. `GET /products/{id}`, `GET /user`) – wrapped in `data`:
   ```json
   { "data": { "id": 1, "name": "..." } }
   ```
2. **Paginated list** (e.g. `GET /products`) – Laravel paginator:
   ```json
   {
     "data": [ { ... } ],
     "links": { "first": "...?page=1", "last": "...?page=3", "prev": null, "next": "...?page=2" },
     "meta": {
       "current_page": 1, "from": 1, "last_page": 3, "path": "...",
       "per_page": 12, "to": 12, "total": 30, "links": [ ... ]
     }
   }
   ```
   Use `?page=N` to paginate. Page size is fixed per endpoint; `per_page` is **not** accepted.
3. **Action result** (create / update / delete / auth) – a message plus a named key, **not** wrapped in `data`:
   ```json
   { "message": "Product created successfully.", "product": { ... } }
   ```

### Errors

| Status | When | Body |
|---|---|---|
| 401 | Missing/invalid token on a protected route | `{ "message": "Unauthenticated." }` |
| 403 | Not admin / not super admin / not your order | `{ "message": "Unauthorized." }` |
| 404 | ID in URL does not exist (or wrong role, see admin users/customers) | `{ "message": "..." }` |
| 422 | Validation failed or invalid order status transition | see below |
| 400 | Role change to the role the user already has | `{ "message": "User already has the specified role." }` |
| 500 | DB constraint violation (see [Known issues](#known-issues)) | `{ "message": "Server Error" }` |

Validation error (`422`):

```json
{
  "message": "The email field is required.",
  "errors": { "email": ["The email field is required."] }
}
```

### Data types to watch

- **Money** (`unit_price`, `price_snapshot`) is returned as a **string**, e.g. `"17800.00"`. Use `Number(...)` before doing arithmetic.
- **`quantity`** is returned as a **string** too (`"2"`), because the column is `VARCHAR` (see schema).
- **Dates** are ISO 8601 UTC strings: `"2026-10-12T09:30:00.000000Z"`.
- **Images** are absolute URLs (`http://localhost:8000/storage/products/abc.jpg`) or `null`. They require `php artisan storage:link` to have been run on the server.
- Seeded content (category/product names, addresses) is in **Arabic**.

---

## Authentication

Laravel Sanctum **personal access tokens** (bearer tokens). Tokens do not expire (`sanctum.expiration = null`); they are revoked only by `POST /logout`.

```js
headers: { Accept: "application/json", Authorization: `Bearer ${token}` }
```

`register` and `login` both return a fresh token. Every login creates a **new** token; old ones stay valid.

### Roles

| Role | Can access |
|---|---|
| `customer` | Public + customer endpoints. Default for everyone who registers. |
| `admin` | Everything under `/admin/*` except changing roles. |
| `super_admin` | Everything, including `PATCH /admin/users/{id}/role`. Only creatable via DB/seeder. |

**The `role` field is only included in a user object when the *requesting* user is an admin.**
Consequences for the frontend:
- `POST /login` / `POST /register` responses never contain `role` (the requester is not authenticated yet).
- To find out whether the logged-in user is an admin: call `GET /user` after login; if `data.role` is present and is `admin`/`super_admin`, they are an admin. A customer's `GET /user` has no `role` key at all.

### Dev seed accounts

Created by `php artisan migrate --seed` (local development only):

| Email | Password | Role |
|---|---|---|
| `admin@example.com` | `password` | `super_admin` |
| `test@example.com` | `password` | `customer` |

---

## Endpoints

| Method | Endpoint | Auth | Shape | Page size |
|---|---|---|---|---|
| POST | `/register` | – | action (`user`, `token`) | |
| POST | `/login` | – | action (`user`, `token`) | |
| GET | `/products` | – | paginated Product | 12 |
| GET | `/products/{id}` | – | single Product | |
| GET | `/user` | token | single User | |
| PATCH | `/user` | token | action (`user`) | |
| POST | `/logout` | token | action | |
| GET | `/orders` | token | paginated Order | 12 |
| POST | `/orders` | token | action (`order`) | |
| GET | `/orders/{id}` | token | single Order | |
| GET | `/admin/dashboard` | admin | raw object | |
| GET | `/admin/products` | admin | paginated Product | 12 |
| POST | `/admin/products` | admin | action (`product`) | |
| GET | `/admin/products/{id}` | admin | single Product | |
| PATCH | `/admin/products/{id}` | admin | action (`product`) | |
| DELETE | `/admin/products/{id}` | admin | action | |
| GET | `/admin/categories` | admin | paginated Category | 12 |
| POST | `/admin/categories` | admin | action (`category`) | |
| GET | `/admin/categories/{id}` | admin | single Category | |
| PUT | `/admin/categories/{id}` | admin | action (`category`) | |
| DELETE | `/admin/categories/{id}` | admin | action | |
| GET | `/admin/orders` | admin | paginated Order | 10 |
| GET | `/admin/orders/{id}` | admin | single Order | |
| PATCH | `/admin/orders/{id}` | admin | action (`order`) | |
| GET | `/admin/customers` | admin | paginated User | 10 |
| GET | `/admin/customers/{id}` | admin | single User | |
| GET | `/admin/admins` | admin | paginated User | 10 |
| GET | `/admin/admins/{id}` | admin | single User | |
| PATCH | `/admin/users/{id}/role` | super_admin | action (`user`) | |

There is **no public categories endpoint** (it is commented out in `routes/api.php`). The store currently hard-codes category IDs 1–3 matching the seeder.

### Auth

#### `POST /register`

| Field | Rules |
|---|---|
| `name` | required, string, max 255 |
| `email` | required, valid email, lowercase, max 255, unique |
| `password` | required, min 8, must match `password_confirmation` |
| `password_confirmation` | required |
| `phone_number` | required, unique, Egyptian mobile: `^01[0125][0-9]{8}$` (e.g. `01012345678`) |
| `address` | required, string, max 1000 |

`201`:
```json
{
  "message": "Registration successful.",
  "user": { "id": 3, "name": "Ahmed Ali", "email": "ahmed@example.com", "phone_number": "01012345678", "address": "Cairo" },
  "token": "3|xYz..."
}
```

#### `POST /login`

`{ "email": "...", "password": "..." }` → `200`:
```json
{ "message": "Login successful.", "user": { ...User }, "token": "4|aBc..." }
```
Wrong credentials → `422` with `errors.email = ["The provided credentials are incorrect."]`.

#### `GET /user`
→ `{ "data": User }`

#### `PATCH /user`
Only these two fields can be changed (name/email/password cannot be changed via the API):

| Field | Rules |
|---|---|
| `phone_number` | optional; same regex as register; unique (ignoring yourself) |
| `address` | optional; string, max 1000 |

→ `{ "message": "Profile updated successfully.", "user": User }`

#### `POST /logout`
Revokes the token used for this request. → `{ "message": "Logged out successfully." }`

### Products (public)

#### `GET /products`

| Query | Description |
|---|---|
| `page` | page number |
| `category_id` | optional; must exist, otherwise `422` |

Sorted newest first. Each product includes its `category`. No search / price filter / sort parameter exists.

#### `GET /products/{id}`
→ `{ "data": Product }` (with `category`).

### Orders (customer)

#### `POST /orders`

```json
{
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 5, "quantity": 1 }
  ]
}
```

| Field | Rules |
|---|---|
| `items` | required array, at least 1 |
| `items.*.product_id` | required integer, must exist |
| `items.*.quantity` | required integer ≥ 1 |

The customer's **name, phone and address come from their profile**; there are no delivery fields on the order. Prices are taken from the DB at order time (the client cannot send prices). The order starts as `pending`, and every admin/super_admin gets a database + email notification (queued — requires the queue worker).

→ `201`:
```json
{
  "message": "Order created successfully.",
  "order": {
    "id": 12,
    "status": "pending",
    "created_at": "2026-10-12T09:30:00.000000Z",
    "user": { ...User },
    "order_items": [
      { "id": 30, "quantity": "2", "unit_price": "17800.00", "price_snapshot": "35600",
        "product": { ...Product } }
    ]
  }
}
```

#### `GET /orders`
Current user's orders, newest first, each with `order_items` and their `product`. No `user` key.

#### `GET /orders/{id}`
→ `{ "data": Order }`. `403` if the order belongs to another user.

**Order total** is not stored or returned — compute it as `sum(Number(item.price_snapshot))`.

### Admin

All `/admin/*` routes require a token of a user with role `admin` or `super_admin`; otherwise `403`.

#### `GET /admin/dashboard`

Not wrapped. Example:
```json
{
  "usersCount": 15,
  "ordersCount": 40,
  "productsCount": 10,
  "categoriesCount": 3,
  "pendingOrders": 5,
  "processingOrders": 3,
  "completedOrders": 30,
  "cancelledOrders": 2,
  "salesLabels": ["May", "Jun", "Jul", "Aug", "Sep", "Oct"],
  "salesData": [0, 125000, 98000, 0, 210000, 45000],
  "categoryLabels": ["خراف", "عجول", "ماعز"],
  "categoryData": [4, 3, 3]
}
```
- `usersCount` counts **all** users including admins.
- `salesData[i]` = sum of `price_snapshot` of items in **completed** orders created in that month (last 6 months, oldest first).
- `categoryLabels`/`categoryData` = product count per category, descending.

#### Products

`POST /admin/products` — **`multipart/form-data`** (contains a file):

| Field | Create | Update (`PATCH`) |
|---|---|---|
| `name` | required, string, max 255 | optional |
| `category_id` | required, existing category | optional |
| `unit_price` | required, numeric, **min 100** | optional, numeric, **min 0** |
| `unit` | optional, max 50, default `"0.5kg"` | optional |
| `description` | optional / nullable | optional / nullable |
| `image` | **required**, jpg/jpeg/png/webp, max 2 MB | optional; replaces and deletes the old file |

Update with no fields → `422 "At least one field must be provided for update."`

> **Uploading an image on update:** PHP does not parse multipart bodies on `PATCH`. Send a `POST` to `/admin/products/{id}` with `_method=PATCH` in the form data, or send JSON (no image) with a real `PATCH`.

`DELETE /admin/products/{id}` deletes the product and its image file. **This also deletes every order item that referenced the product** (see schema: cascade).

`GET /admin/products` is not sorted (DB order).

#### Categories

| Endpoint | Body |
|---|---|
| `POST /admin/categories` | `{ "name": "..." }` required, max 255 |
| `PUT /admin/categories/{id}` | `{ "name": "..." }` required, max 255 |
| `DELETE /admin/categories/{id}` | – |

Category responses contain only `id` and `name` (no product list, no product count).

#### Orders

`GET /admin/orders` — all orders, newest first, with `user` and `order_items.product`.
`GET /admin/orders/{id}` — same, single.

`PATCH /admin/orders/{id}` — `{ "status": "processing" }`. Allowed transitions:

```
pending ──► processing ──► completed
   │             │
   └──► cancelled ◄┘
```

`completed` and `cancelled` are final. Any other transition (including setting the same status) → `422`:
`{ "message": "Invalid status transition from 'pending' to 'completed'." }`.
The `order` in the response does **not** include `user`/`order_items`.

#### Customers

`GET /admin/customers` — users with role `customer`, newest first, each with `orders_count`.
`GET /admin/customers/{id}` — the customer, `orders_count`, and their **latest 5** `orders` (with items and products; no pagination meta). `404` if the user is not a customer.

#### Admins

`GET /admin/admins` — users with role `admin` or `super_admin`, newest first.
`GET /admin/admins/{id}` — `404` if the user is not an admin.

#### Change role (super admin only)

`PATCH /admin/users/{id}/role` — `{ "role": "admin" | "customer" }`.
`super_admin` cannot be granted via the API. Same role as current → `400`.
→ `{ "message": "User role updated successfully.", "user": User }`

---

## Response objects

Keys marked *conditional* appear only in the situations described.

### User
```json
{
  "id": 1,
  "name": "Ahmed Ali",
  "email": "ahmed@example.com",
  "phone_number": "01012345678",
  "address": "Cairo",
  "role": "customer",        // conditional: requester is admin
  "orders_count": 4,         // conditional: admin customer endpoints
  "orders": [ Order ]        // conditional: admin customer detail (latest 5)
}
```
Not exposed: `password`, `remember_token`, `email_verified_at`, timestamps.

### Category
```json
{ "id": 1, "name": "خراف" }
```

### Product
```json
{
  "id": 1,
  "name": "خروف بلدي",
  "description": "خروف بلدي مربى على البرسيم والشعير.",
  "unit_price": "17800.00",
  "unit": "رأس",
  "image": "http://localhost:8000/storage/products/abc.jpg",   // or null
  "category": { "id": 1, "name": "خراف" }                       // conditional: always loaded by product endpoints
}
```
Not exposed: `category_id`, timestamps. Seeded products have `image: null`.

### Order
```json
{
  "id": 12,
  "status": "pending",          // pending | processing | completed | cancelled
  "created_at": "2026-10-12T09:30:00.000000Z",
  "user": User,                 // conditional: admin order endpoints, POST /orders
  "order_items": [ OrderItem ]  // conditional: all reads except PATCH /admin/orders
}
```

### OrderItem
```json
{
  "id": 30,
  "quantity": "2",
  "unit_price": "17800.00",      // product price at order time
  "price_snapshot": "35600",     // line total = unit_price × quantity at order time
  "product": Product             // current product data (name/image may have changed since)
}
```

---

## Database schema

MySQL (`DB_CONNECTION=mysql`, database `laravel` locally). All `id` columns are `BIGINT UNSIGNED AUTO_INCREMENT`; all `created_at`/`updated_at` are nullable `TIMESTAMP`s managed by Laravel.

### Entity relationships

```
users 1 ──< orders 1 ──< order_items >── 1 products >── 1 categories
  │
  ├──< phone_verifications
  ├──< personal_access_tokens   (polymorphic: tokenable)
  └──< notifications            (polymorphic: notifiable)
```

### Application tables

#### `users`
| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| name | varchar(255) | no | | |
| email | varchar(255) | no | | **unique** |
| email_verified_at | timestamp | yes | null | not used by the API |
| password | varchar(255) | no | | bcrypt hash |
| phone_number | varchar(20) | no | | uniqueness enforced only by API validation, **no DB unique index** |
| address | text | no | | added by `add_address_to_users_table` |
| role | enum(`super_admin`,`admin`,`customer`) | no | `customer` | |
| remember_token | varchar(100) | yes | null | web sessions only |
| created_at / updated_at | timestamp | yes | | |

#### `categories`
| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| name | varchar(255) | no | | **unique** |
| created_at / updated_at | timestamp | yes | | |

#### `products`
| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| category_id | bigint unsigned | no | | FK → `categories.id`, **ON DELETE RESTRICT** |
| name | varchar(255) | no | | |
| description | text | yes | null | |
| unit_price | decimal(8,2) | no | | max 999,999.99 |
| unit | varchar(255) | no | `0.5kg` | seeded products use `رأس` (head) |
| image | varchar(255) | yes | null | path on `public` disk, e.g. `products/abc.jpg` |
| created_at / updated_at | timestamp | yes | | |

#### `orders`
| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| user_id | bigint unsigned | no | | FK → `users.id`, **ON DELETE CASCADE** |
| status | enum(`pending`,`processing`,`completed`,`cancelled`) | no | `pending` | `processing` added by `add_processing_status_to_orders_table` |
| created_at / updated_at | timestamp | yes | | |

No total, address or phone columns: the total is derived from `order_items`; contact details are read live from `users`.

#### `order_items`
| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| order_id | bigint unsigned | no | | FK → `orders.id`, **ON DELETE CASCADE** |
| product_id | bigint unsigned | no | | FK → `products.id`, **ON DELETE CASCADE** |
| unit_price | decimal(8,2) | no | | product price at order time |
| quantity | **varchar(255)** | no | | integer stored as string |
| price_snapshot | **varchar(255)** | no | | line total (`unit_price × quantity`) stored as string |
| created_at / updated_at | timestamp | yes | | |

#### `phone_verifications`
Used by the OTP phone-change flow, which is currently **disabled** (routes and controller methods are commented out). Table exists but nothing writes to it.

| Column | Type | Null | Default | Notes |
|---|---|---|---|---|
| id | bigint unsigned | no | auto | PK |
| user_id | bigint unsigned | no | | FK → `users.id`, ON DELETE CASCADE |
| phone_number | varchar(20) | no | | requested new number |
| code | varchar(6) | no | | 6-digit OTP |
| attempts | tinyint unsigned | no | 0 | max 5 |
| expires_at | timestamp | no | | created + 10 min |
| verified_at | timestamp | yes | null | |
| invalidated_at | timestamp | yes | null | set when superseded or too many attempts |
| created_at / updated_at | timestamp | yes | | |

#### `notifications`
Laravel database notifications. One row per admin per new order (`NewOrderNotification`).

| Column | Type | Notes |
|---|---|---|
| id | char(36) | UUID, PK |
| type | varchar(255) | `App\Notifications\NewOrderNotification` |
| notifiable_type / notifiable_id | varchar / bigint | → `App\Models\User` / `users.id` (indexed together) |
| data | text | JSON: `{ order_id, customer_name, phone, address, total, message }` |
| read_at | timestamp, nullable | |
| created_at / updated_at | timestamp | |

No API endpoint reads notifications yet.

#### `personal_access_tokens`
Sanctum tokens. `tokenable_type`/`tokenable_id` → user; `name` is always `api-token`; `token` is a unique SHA-256 hash (varchar 64); `abilities`, `last_used_at`, `expires_at` (nullable, indexed).

### Framework tables (no app logic)

| Table | Purpose |
|---|---|
| `password_reset_tokens` | web password resets (PK `email`) |
| `sessions` | web session store (`SESSION_DRIVER=database`) |
| `cache`, `cache_locks` | cache store |
| `jobs`, `job_batches`, `failed_jobs` | queue (`QUEUE_CONNECTION=database`); holds pending notification emails |

### Seed data

`php artisan migrate --seed` creates the two users above (both with phone `01000000000`, address `القاهرة`) and three categories **in this order** (the client relies on IDs 1–3):

| id | Category | Products |
|---|---|---|
| 1 | خراف (sheep) | 4 |
| 2 | ماعز (goats) | 3 |
| 3 | عجول (calves) | 3 |

---

## Business rules

- Prices are frozen per order item (`unit_price`, `price_snapshot`); changing a product's price does not change past orders.
- Customer contact details are **not** frozen; editing a profile changes what old orders show.
- Customers can only read their own orders; they cannot cancel or edit them.
- Only admins change order status, along the transition graph above.
- A new order notifies all `admin`/`super_admin` users by DB row + email (email is written to the log locally, `MAIL_MAILER=log`).

---

## Known issues

Behaviour the frontend/DB work should be aware of until fixed in the backend:

1. **Deleting a category that still has products returns 500** (FK `RESTRICT`); no friendly 422 is returned. Move or delete its products first.
2. **Creating/renaming a category to an existing name returns 500** (DB unique index, but no `unique` validation rule).
3. **Deleting a product deletes its order items** (`order_items.product_id` cascades), which silently changes past orders' totals and dashboard sales. Consider `restrictOnDelete` or soft deletes.
4. `order_items.quantity` and `price_snapshot` are `VARCHAR`; should be `unsigned int` and `decimal(10,2)`. `SUM()` works in MySQL via implicit cast, but sorting/comparison is string-based.
5. `products.unit_price` / `order_items.unit_price` are `decimal(8,2)` (max 999,999.99); line totals above that can't be stored if `price_snapshot` is ever made decimal(8,2).
6. `users.phone_number` has no DB unique index (the seeder itself creates two users with the same number).
7. `unit_price` minimum is 100 on create but 0 on update.
8. `GET /admin/categories/{id}` runs an unused paginate query and returns no products; `GET /admin/categories` computes `products_count` but does not return it.
9. Login/register responses never include `role` (see [Roles](#roles)).
10. A super admin can change another `super_admin`'s role (including their own) to `admin`/`customer`.
