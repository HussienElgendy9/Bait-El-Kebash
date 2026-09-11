# Verification record — 2026-09-11

No application database was migrated or reset, no deployment/push occurred, and no real notification was sent. The repository was inspected before implementation; no applicable AGENTS.md was found at repository/ancestor paths. All original api-migration documents and existing routes/controllers/models/migrations/notifications/configuration/tests were read.

## Baseline

Command: `php vendor/phpunit/phpunit/phpunit --log-junit storage/logs/baseline-tests.xml`

Result: **25 tests, 6 assertions, 19 errors, 2 failures** under PHP 8.2.12 / PHPUnit 11.5.55, with SQLite `:memory:` and array mail. The factory omitted required phone/address (19 database errors); registration fixtures omitted required fields; the home-page test had no database initialization. Stale dashboard route expectations were also found after factory repair.

During implementation, concurrent workspace work converted tests to Pest, changed dependencies and replaced some web scaffolding. The user explicitly authorized reconciling those files to preserve MVC compatibility while retaining Pest. These dependency/UI changes were not reset. The final installed versions are Laravel 12.62.0, Sanctum 4.3.3, Pest 3.8.7 and PHPUnit 11.5.56.

Additional source-confirmed MVC failures were fixed: dashboard merge-conflict markers and invalid variables, order detail relationship/debug output, item-versus-order route ambiguity, user relation/role updates, and layout compatibility with both component slots and Blade sections. Original public/admin/cart routes were reconciled with the new scaffolding.

## Final commands and actual results

| Command/check | Result |
| --- | --- |
| `php vendor/bin/pest tests/Feature/Api --compact --log-junit storage/logs/api-tests.xml` | **39 passed, 566 assertions**, 6.38 s |
| `php vendor/bin/pest --compact --log-junit storage/logs/migration-tests.xml` | **64 passed, 627 assertions**, 8.20 s |
| `php vendor/bin/pint --test` with the explicit path list in `docs/api/check-format.ps1` | **passed** |
| `php artisan route:list --json` | Exit 0; **124 total route definitions** |
| `php artisan route:list --path=api/v1 --except-vendor` | Exit 0; **48 v1 route definitions** (PUT/PATCH share definitions) |
| `npm.cmd exec --offline --cache ./storage/api-validation-cache --yes --package @apidevtools/swagger-cli@4.0.4 -- swagger-cli validate docs/api/openapi.yaml` | **docs/api/openapi.yaml is valid** |
| `git diff --check` | Exit 0; no whitespace errors |
| PowerShell AST parsing of `docs/api/examples.ps1` and `docs/api/check-format.ps1` | **0 parse errors** |
| Scan for merge-conflict markers in application/routes/database/tests/views | None remaining |

The validator was downloaded into an ignored repository-local cache after the normal network request was denied. No application dependencies were changed for validation. Swagger CLI reports its upstream deprecation warning but successfully validates OpenAPI 3.0.3. Initial YAML empty-sequence serialization errors were corrected and the validation was rerun successfully.

The current Windows execution policy blocks direct `.ps1` invocation. Formatting was therefore verified by executing the same `php vendor/bin/pint --test` command/path list directly in the shell. The request example was syntax checked; it was not used against the user's running application or account. Review scripts and use your permitted local script-execution policy when running them.

## Meaningful coverage

- Real encrypted cookie jar with the framework's CSRF branch enabled: initialization, rejected missing token, login session-ID rotation, profile mutation, logout/replay, and invalidation of another browser session after password change. No middleware was disabled to make these tests pass.
- CORS allowlisted origin/credentials and untrusted-origin rejection. A single configured origin can appear as the response allow-origin even for a disallowed request; the test checks that the untrusted origin is never authorized.
- Register/login/profile privilege filtering, credential validation and throttling, existing bearer access/revocation, password confirmation/change/reset/expiry/replay, signed email ownership/signature/expiry and fake delivery.
- Catalog pagination/filtering/resources, admin read/write authorization, image content/type limits, multipart replacement/removal, customer/admin order ownership, status/item edits and dashboard totals, role and deletion guards, recipient-scoped notification reads.
- Exact checkout totals, immutable new snapshots, null old snapshots, quantity/duplicate/extra-field limits, partial-line rollback, post-commit notifications and delivery failure isolation, same-key replay/conflict, replay after catalog replacement/deletion.
- Independent PHP processes using unique disposable SQLite fixtures verify concurrent identical checkout (one order), same-challenge phone consumption (one success), and competing phone claims (one owner). These tests exposed PHP 8.2 SQLite lock behavior; explicit writer acquisition fixed it.
- Migration preflight rejects duplicate phones and fractional historical quantities before DDL; valid history survives with unknown snapshots left null and old plaintext challenges erased/invalidated.
- OpenApiTest checks every v1 route/method in both directions and a concrete resource's required fields/money format. Full OpenAPI structural/reference validation is performed separately by Swagger CLI.

## Limits and release prerequisites

Runtime database tests cover SQLite, including true multi-process races. MySQL/MariaDB/PostgreSQL deployment DDL and locking must be rehearsed against the actual production engine/version; no production engine was accessed here. The migration is forward-only and requires a backup, writer freeze and data review before deployment.

SMS is an explicitly deferred external adapter/credential dependency and returns 503 by default. Production mail, provider delivery, public storage hosting, actual browser domains, and React reset/verification screens require integration acceptance. Notification failures are isolated/logged; durable outbox/retry delivery is not implemented. No load test, visual redesign, React build, real-message test or production deployment is claimed.
