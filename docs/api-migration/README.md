# Backend API migration handoff

The backend milestones are implemented in Laravel under `/api/v1`. No React application was built or deployed. MVC storefront/admin pages and the unversioned API remain available. The initial milestone-1-only documentation is superseded by this handoff.

Start with [local setup and requests](05-local-setup.md), the [OpenAPI contract](../api/openapi.yaml), [feature parity](06-feature-parity.md), and [deployment/cutover](07-deployment-cutover.md). [Verification](08-verification.md) records the baseline, exact commands, and environment limits.

Implemented: catalog/filtering/pagination, session cart, cookie/CSRF authentication, customer profile and account recovery, hashed/atomic phone changes, exact/idempotent checkout with snapshots, order history, and admin catalog/images/categories/orders/items/users/roles/dashboard/notifications.

Phone delivery is deliberately **unavailable** until a real `PhoneSender` adapter and credentials are configured; the API returns 503. Email requests also return 503 until mail is explicitly enabled and a working transport exists. Fake delivery is confined to tests. These external integrations are not represented as completed production delivery.

Same-site deployment is assumed (for example, shop.example.test and api.example.test). Currency and stricter status/retention business policies require confirmation before launch; the implementation preserves existing units, integer quantities and the three existing order states. It adds no payment, stock, tax, shipping charge or discount system.

