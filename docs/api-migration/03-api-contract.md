# Implemented API contract

The authoritative machine-readable contract is [docs/api/openapi.yaml](../api/openapi.yaml). A test compares every versioned route/method against it in both directions, including the documented multipart POST method override.

- Resource reads/writes use `data`; paginated lists use `data`, `links`, `meta`. Default 12/page, maximum 100. Invalid pagination returns 422.
- Money is an exact decimal string with two fractional digits; quantities are integers. Product units default to the existing `0.5kg` and can be explicitly managed.
- Validation errors: `{"message":"...","errors":{"field":["..."]}}`. Other errors have `message`. API failures use JSON even without Accept. Production must use APP_DEBUG=false.
- Status codes: 200 read/update/login/replay; 201 create/register; 204 empty success; 401 unauthenticated; 403 forbidden; 404 absent/nested mismatch; 409 uniqueness/history/idempotency conflicts; 419 CSRF/session; 422 input; 429 throttling; 503 unavailable integrations.
- Auth is cookie/session for the first-party SPA. GET /sanctum/csrf-cookie, then send credentials, configured Origin, and X-XSRF-TOKEN on mutations. V1 login/register issue no bearer tokens. Existing bearer consumers continue on legacy routes and supported protected v1 routes.
- Session-only operations: login/register/recovery, session cart, password confirmation/change and self-deletion. Existing token users can manage profile/phone, read/create orders, and use admin endpoints when authorized.
- Registration ignores supplied roles. Own profile only accepts name/email/address. Email changes clear verification. Phone changes require a separate challenge; registration itself does not establish phone possession.
- Email verification remains optional for checkout, matching the existing application's lack of an enforced email-verification contract. Request verification explicitly. The signed URL requires the owning authenticated user.
- Checkout accepts 1–100 distinct products, each quantity 1–1000. The server owns price, status, user and delivery snapshots. `Idempotency-Key` is required by v1, 1–100 ASCII letters/digits/period/underscore/colon/hyphen, case-sensitive and hashed before persistence. Same user/key/items replays with 200; changed items returns 409. Items are normalized by ID and integer quantity. Replay returns the current stored order, including later admin edits; it does not reprice or notify again.
- Legacy checkout accepts an optional `idempotency_key` body field; without it repeated requests intentionally create separate orders for compatibility.
- Old delivery/product-name/unit snapshots remain null: current data is not evidence of historical values. New snapshots survive later catalog/profile changes. Same-product admin item edits retain price/snapshots; replacement products use current price/name/unit.
- Admin status edits preserve the existing permissive transitions between pending/completed/cancelled. Order IDs and nested item IDs are separate. No order-history deletion API is offered because original destroy actions were empty.
- Category deletion is blocked while products exist. Product deletion is blocked if any order references it. User deletion is blocked for any order history; admin/self role protections also apply. Restrictive foreign keys protect direct SQL/cross-request races.
- Images: JPEG/PNG/WebP, maximum 2048 KiB, public disk. Use multipart POST for creates; for updates POST to /admin/products/{id} with `_method=PATCH`. Replacement/removal deletes the previous file after successful database save. SVG is rejected.

See endpoint schemas/descriptions for exact request fields, filters, access and response envelopes. Unknown top-level fields are ignored unless specifically validated; checkout line objects reject extra fields.

