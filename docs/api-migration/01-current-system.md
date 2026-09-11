# Current system and migration risks

> Historical assessment from before implementation. The findings below were verified against source and addressed by the expanded migration. For current behavior, see README.md, the OpenAPI contract and the feature-parity matrix. Concurrent scaffolding edits were reconciled with MVC compatibility by user instruction.

## Stack and code map

- `composer.json`: PHP ^8.2, Laravel ^12.0, Sanctum ^4.0, Breeze ^2.4, PHPUnit ^11.5.50. These are declared constraints; inspect composer.lock for exact installed versions.
- `package.json`: Vite, Blade-related Laravel tooling, Alpine, Tailwind, Axios and Chart.js. React is not declared.
- `bootstrap/app.php`: loads both web and API routes; registers `admin` middleware. Does not enable Sanctum stateful API middleware.
- `routes/web.php`, `routes/auth.php`: storefront, session cart, admin pages, Breeze auth, profile.
- `app/Http/Controllers/Api`: existing JSON auth, catalog, customer orders and partial admin functionality.
- `app/Http/Controllers/Store` and `Admin`: existing business behavior embedded in view/redirect controllers.
- `resources/views`, `resources/js/store`: current UI references for the later React migration.
- `app/Notifications/NewOrderNotification.php`: database and mail notification with an MVC admin URL.
- `tests/Feature`: primarily scaffold authentication/profile tests; no dedicated API feature suite found.

## Existing route inventory

All API paths below are prefixed `/api`; none is versioned.

| Access | Methods and paths | Current behavior |
| --- | --- | --- |
| Public | POST /register, /login | Returns raw serialized user plus personal access token |
| Public | GET /products, /products/{product} | Category eager loaded; list paginated at 12 |
| Public | GET /categories, /categories/{category} | List paginated at 12; detail loads all products |
| Sanctum | GET /user, PATCH /user | Own user; PATCH only changes address |
| Sanctum | POST /user/phone/request, /user/phone/verify | Phone change OTP workflow |
| Sanctum | POST /logout | Deletes current access token; assumes token exists |
| Sanctum | GET /orders, POST /orders, GET /orders/{order} | Own orders, transactional creation, ownership check |
| Sanctum + admin | GET /admin/dashboard | Counts and six-month sales/category chart arrays |
| Sanctum + admin | GET /admin/products | Currently routes to public API ProductController |

Web routes expose storefront browsing, authenticated session cart operations and checkout, admin product/category/order resources, admin user management, and profile/auth pages. Resource declarations do not guarantee implemented actions: several controller methods are empty or absent.

## Data and business behavior

| Entity | Important fields and relationships |
| --- | --- |
| User | name, unique email, password, phone_number, address, role admin/customer; `order()` relation is singular-named |
| Category | unique name; has many products |
| Product | category_id, name, description, decimal(8,2) unit_price, unit default `0.5kg`, nullable image; no stock field |
| Order | user_id; pending/cancelled/completed status; `orderitems()` relation |
| OrderItem | order_id, product_id, decimal(8,2) unit_price; quantity and price_snapshot stored as strings |
| PhoneVerification | user_id, proposed phone, six-character plaintext code, expiry, verified/invalidated timestamps, attempts |

API registration requires name, lowercase unique email, confirmed password, Egyptian-format phone (`^01[0-9]{9}$`) and address. Role defaults to customer in the schema. Registration passes role to create(), but User does not make role fillable. Keep privilege changes explicit and restricted.

API checkout accepts `items: [{product_id, quantity}]`, with positive integer quantities. It reads current product prices and creates all lines within a transaction. `price_snapshot` means the line total, not unit price. MVC checkout uses prices cached in the session cart and notifies admins after committing; API checkout currently does not notify. No stock, payment gateway, shipping fee, tax or discount implementation was identified in the inspected backend. Currency and fractional-weight requirements are unconfirmed.

## Source-confirmed findings

1. Phone request returns the OTP in JSON and stores it plaintext; no SMS delivery call exists in that method, despite a sent-success message. Existing expiry, ownership, invalidation and five-attempt checks are useful but do not make this production verification.
2. No explicit API login/register/OTP throttles are declared in routes/api.php. OTP verification updates are not transactional or locked; the phone uniqueness validation happens before verification and users.phone_number has no unique index in the inspected migrations.
3. `/api/admin/products` imports the public controller. The separate API Admin ProductController is unused there and its store method is empty. Admin category/order/user write APIs are absent.
4. API responses use inconsistent root keys and direct model serialization. User hides role, so a future SPA cannot obtain it through current user serialization. Expose it intentionally only in an authenticated user resource; backend authorization remains authoritative.
5. API logout assumes a persisted access token. A cookie-authenticated SPA needs session logout and CSRF handling instead.
6. MVC Admin OrderController.show refers to nonexistent `orderitem` and contains `dd()`. Its update method treats the route id as an OrderItem id despite being an order resource. Do not port this ambiguity into the API.
7. MVC Admin UserController.destroy refers to `orders`, but User defines `order()`. Role update uses mass assignment while role is not fillable. These are migration hazards to test, not patterns to copy.
8. Foreign keys cascade category deletion to products, product deletion to order items, and user deletion to orders. Blind CRUD migration can erase order history.
9. Money arithmetic currently uses multiplication without explicit exact-decimal handling. String line totals and quantities need a data-aware migration before schema tightening.
10. UserFactory omits required phone_number and address. Existing scaffold tests may fail with current schema; runtime baseline has not been executed for this documentation task.
11. Orders do not snapshot delivery contact/address or product name/unit. Later profile/catalog edits can change how historical orders are presented.

## Verification limits

Working tree was clean before docs creation. PHP and Composer were discoverable; vendor/autoload.php exists. No application tests, migrations, database inspection or production integration calls were executed. No environment secrets were read. Production database engine, deployment domains, active API consumers, mail/SMS configuration and actual data quality remain unverified.
