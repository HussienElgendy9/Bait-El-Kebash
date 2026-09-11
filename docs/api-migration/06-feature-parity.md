# Feature parity matrix

Paths below use /api/v1 unless noted. All listed replacements are implemented. Test names refer to tests/Feature/Api; existing Pest auth/profile tests remain.

| Existing capability | API replacement | Verification | External dependency / notes |
| --- | --- | --- | --- |
| Home latest eight products + categories | GET /products?per_page=8 and GET /categories | CatalogTest, CompatibilityTest | React renders the composition |
| Shop listing and product details | GET /products, /products/{product}; category/q/sort filters | CatalogTest | None |
| Category list/details | GET /categories, /categories/{category}; product filter | CatalogTest | Category products are paginated through /products |
| Session cart display/add/decrease/remove/clear | GET/DELETE /cart, POST /cart/items, PATCH/DELETE /cart/items/{product} | CompatibilityTest | Session-only; PATCH sets quantity, enabling decrease |
| MVC and legacy checkout | POST /orders; shared CheckoutService used by all three surfaces | CheckoutTest, CompatibilityTest, ConcurrencyTest | No payment/inventory integration exists |
| Customer order history/details (legacy API; MVC controller empty) | GET /orders, /orders/{order} | CheckoutTest | Owned orders only |
| Register/login/logout/current user | /auth/register, /auth/login, /auth/logout, /me | AccountTest, BrowserSessionTest | Same-site session/CORS config |
| Profile name/email and address updates | PATCH /me | AccountTest | Email changes clear verification |
| Profile deletion | DELETE /me with password | AccountTest, AdminTest; existing ProfileTest | Any history/admin role blocks deletion |
| Password confirmation/change | POST /auth/confirm-password, PUT /me/password | AccountTest | Session-only |
| Forgot/reset password | POST /auth/forgot-password, /auth/reset-password | AccountTest | Real mail + React reset page; unavailable=503 |
| Email verification prompt/resend/consume | GET /me reports status; POST /me/email/verification-notification; GET /auth/verify-email/{id}/{hash} | AccountTest | Real mail + React verification page; not a checkout gate |
| Phone change request/verify (legacy API) | POST /me/phone/request, /me/phone/verify | PhoneTest, ConcurrencyTest | SMS adapter/credentials missing; explicit 503 |
| Admin dashboard counts/status counts/monthly sales/category charts | GET /admin/dashboard | CatalogTest, AdminTest | Exact totals, last six calendar months |
| Admin products list/create/edit/delete + images | /admin/products CRUD, multipart method override | AdminTest, CompatibilityTest | Public disk + storage:link; protected deletion |
| Admin category list/create/edit/delete | /admin/categories CRUD | AdminTest | Unique names; nonempty deletion blocked |
| Admin order listing/details/edit status | GET /admin/orders, /admin/orders/{order}; PATCH/PUT /admin/orders/{order} | AdminTest, CompatibilityTest | Permissive transitions preserve original states |
| Admin edit product/quantity in an order line | PATCH /admin/orders/{order}/items/{item} | AdminTest, CompatibilityTest | Nested ownership; historical same-product price |
| Admin user listing/details/role changes/delete | /admin/users, /admin/users/{user} GET/PATCH/PUT/DELETE | AdminTest | Self/admin/history guards; role update fixed |
| Administrator new-order database/mail notification | Shared post-commit OrderNotifier | CheckoutTest | Database notice works; real mail is configurable; failures logged |
| Notification read/list operations | GET /admin/notifications; PATCH /{notification}; POST /read-all | AdminTest | Recipient-scoped; adds usable API access to stored notices |
| Empty/missing MVC resource actions | No fabricated feature | Route inventory + code inspection | Admin order create/store/destroy were empty; product/category show absent. API supplies useful catalog detail but does not invent admin order creation/deletion. |
| Existing bearer clients | Retained /api/register,/api/login,/api/user,/api/logout,/api/products,/api/categories,/api/orders,/api/admin/* | AccountTest, CompatibilityTest | Security fixes apply to legacy phone/checkout; responses otherwise remain legacy |

Cross-cutting checks: BrowserSessionTest exercises real encrypted cookies with CSRF enabled, session rotation/logout and CORS. MigrationTest checks duplicate/invalid historical data and null snapshots. ConcurrencyTest runs separate processes for checkout retries, same-challenge consumption, and competing phone claims. OpenApiTest checks route/method coverage.

Retained MVC repairs include missing orders relationship, explicit role updates, order/item separation, order details, protected deletion, server repricing, registration required fields, and dashboard merge-conflict artifacts. Concurrent Pest conversion and unrelated dependency/UI edits were preserved.

