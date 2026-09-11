# Migration, deployment and cutover

## Before applying the additive migration

- Back up the target database and uploads; rehearse on a restored, access-controlled copy using the actual production engine/version. Runtime verification here used SQLite, not a production MySQL/MariaDB service.
- Stop application writers (including legacy API/MVC and workers) while preflight and DDL run. MySQL DDL is not fully transactional; a partial failure must be reviewed before retrying.
- Resolve duplicate users.phone_number values with the business owner. The migration stops **before DDL** and does not pick a winner or rewrite phone ownership.
- Review order_items quantity and price_snapshot. Only positive integer quantity strings within signed 32-bit range and nonnegative decimal line totals with at most two decimal places are accepted. Ambiguous/fractional/invalid rows stop migration; do not round them automatically.
- Historical order unit_price values are retained as recorded. Existing migrations that added that column are not rewritten. Reconcile older data independently where its origin cannot be proven.
- Run ordinary `php artisan migrate` for the explicitly selected target environment. Never migrate:fresh/reset a real database.
- Verify users.phone_number unique index and restrictive category→product, product→order-item, user→order foreign keys. Foreign keys must stay enabled.
- New nullable order delivery fields and order-item product name/unit fields remain null for pre-existing rows. Quantity becomes unsigned integer; line total becomes decimal(16,2). Existing product prices/units remain unchanged.
- Existing plaintext phone codes are erased and invalidated; users must request fresh challenges. code is widened to hold hashes.
- Order idempotency is persisted with a per-user unique hash key and canonical request hash. Existing orders retain null keys. The migration is forward-only: its down method refuses to discard snapshots or re-enable cascades. Roll back application code only with a compatible schema, or restore a reviewed backup.

## Hosting and external integrations

Deploy frontend/API on the same site, normally HTTPS subdomains sharing a registrable domain. Configure exact CORS_ALLOWED_ORIGINS including schemes/ports; SANCTUM_STATEFUL_DOMAINS uses host[:port] without schemes. Use SESSION_DOMAIN=.example.test for the intended shared domain, SESSION_SECURE_COOKIE=true, SESSION_HTTP_ONLY=true, SESSION_SAME_SITE=lax, APP_DEBUG=false, APP_URL and FRONTEND_URL. Never use wildcard credentialed origins. Different unrelated sites require a separate auth/deployment decision.

Use a shared persistent session/cache store across API instances. Configure trusted proxy/HTTPS handling for your actual host. Run storage:link, verify public image URLs and upload permissions, and keep uploads backed up. APP_KEY must be securely provisioned and consistent across nodes.

SMS requires a production PhoneSender implementation and provider credentials. Default adapter is unavailable and cannot be enabled by pretending to send. API_MAIL_ENABLED=true additionally requires a working supported mail transport; verify reset, verification and admin notifications through an approved staging recipient. No real notifications were sent during this task.

OrderNotifier runs after successful commit, independently for each administrator/channel; failures log order/admin/channel identifiers without secrets and never turn a committed checkout into an HTTP failure. Delivery currently has no durable retry/outbox: monitor these logs and reconcile failed notices. A process crash after commit but before notification processing can require manual notification recovery; orders remain durable and visible in admin. Do not replay checkout with a new key to recover a notification.

SQLite uses a 5-second busy timeout and explicitly acquires its writer lock before mutable state is read. IMMEDIATE mode is also configured, but Laravel only honors it on PHP 8.4+. MySQL uses row locks and uniqueness constraints. Shared checkout/phone/role services retry database transaction concurrency failures up to three times. If a request still fails or its network result is unknown, retry checkout with the same key/body. The tested independent-process races are SQLite-specific; repeat them on the production engine before release.

## Business assumptions to confirm

- Currency is not encoded in the data. Some old UI labels suggest Egyptian pounds, but the API does not assert a currency without an explicit decision.
- Counts are integer multiples of the existing product unit (default 0.5kg), not fractional weights. Maximum checkout: 100 products × 1000 units each; maximum catalog price 999999.99. These are protective technical limits.
- The original admin UI permitted all three known statuses and line edits. This remains; no terminal-state or payment policy is invented.
- Deletion rejects referenced history. Formal anonymization, retention, cancellation rules and stricter role governance need an explicit policy.
- Email verification is opt-in and phone verification proves a change, not initial registration. Enforcing either as a checkout prerequisite would be a separate business change.
- There is no payment, tax, discount, shipping fee, delivery scheduling or inventory system in the original backend.

## Separate cutover checklist

1. Implement and accept React storefront, cart/checkout, auth/account, recovery/verification, and all admin screens against OpenAPI.
2. Verify Arabic/RTL, pagination, unavailable products, image URLs and multipart updates, validation/conflict/error UX.
3. Run browser journeys on the real same-site hosts: cookies, CSRF, CORS, login/logout, expiry, reset/verification links and administrator role changes.
4. Acceptance-test real SMS/mail using explicitly approved recipients, monitor notification failures, and rehearse database/upload backup restoration.
5. Inventory existing bearer clients; publish their migration plan and maintain legacy responses until they have moved.
6. Compare the feature matrix against frontend acceptance tests, observe production usage, and agree on rollback criteria.
7. Only then remove selected Store/Admin MVC controllers/routes and views, Breeze page controllers, legacy Api controllers/routes and obsolete Blade assets. Keep shared services/models/policies, API resources and tests.
8. Repoint notification/reset links and remove transitional code only after link destinations exist. Review legacy token issuance/revocation separately; do not invalidate active consumers prematurely.
9. Remove the old UI build/dependencies only when no retained page needs them. No UI deletion, deployment, push or repository reset was performed here.
