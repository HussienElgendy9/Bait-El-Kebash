# Completed backend milestones and remaining integration

The user's expanded implementation request supersedes the earlier milestone-1-only scope.

1. **API foundation implemented:** /api/v1, explicit resources, Form Requests, ownership/role policies, JSON errors without Accept, bounded pagination, Sanctum stateful middleware, CORS allowlist, session rotation and logout, authentication rate limits, isolated tests.
2. **Customer/account/checkout implemented:** session cart CRUD/quotes; profile name/email/address; password confirmation/change/reset; signed email verification; hashed phone changes with expiry, resend invalidation, bounded attempts, atomic consumption and phone uniqueness; server-priced atomic checkout with persistent per-user idempotency, nullable historical snapshots, post-commit admin notifications.
3. **Admin parity implemented:** dashboard counts and sales/category charts, catalog CRUD and image lifecycle, categories, order listing/detail/status and nested item editing, user listing/detail/roles/deletion guards, own notification listing/read/read-all.
4. **React integration remains:** consume the contract, implement screens and reset/verification landing pages, retain credentials and XSRF handling, and validate same-site deployment and Arabic/RTL rendering. A session cart API exists; a client-managed cart can also submit explicit items to checkout.
5. **Cutover remains:** migrate consumers and verify end-to-end acceptance before retiring MVC/legacy routes. Do not remove current UI ahead of replacement.

External dependencies: configure and acceptance-test a real SMS adapter, mail transport, hosting origins/cookies/storage, and production database deployment. These are explicit release prerequisites, not stub endpoints disguised as delivery.

Architecture: focused v1 controllers; Form Requests validate inputs; Resources expose intentional fields; OrderPolicy enforces customer ownership; UserPolicy and admin middleware restrict management. CheckoutService, OrderEditor, UserManagement, ProductImages and phone delivery/verification services centralize shared behavior. No repository layer or React code was introduced.

Implementation defaults and operational limits are in [deployment/cutover](07-deployment-cutover.md); every existing capability is accounted for in [feature parity](06-feature-parity.md).

