# PHASE 5 — PERFORMANCE & SCALABILITY AUDIT REPORT

Status: completed within Phase 5 only. Phase 6 not started.

## Scope and constraints

- Reviewed the work previously completed in Phase 3B–3H and Phase 4.
- Did not repeat completed remediation work.
- Focused only on verified bottlenecks with safe, non-destructive changes.
- Kept business behavior intact and avoided schema rewrites or destructive migrations.

## Executive summary

The codebase is structurally sound for a mid-size WordPress POS plugin, but two real production bottlenecks were verified and addressed:

1. The notification polling client was calling a missing REST endpoint; this created repeating failed AJAX/REST traffic on every polling cycle.
2. The dashboard chart logic was running a large number of per-bucket SQL queries for revenue series, creating avoidable DB load on realistic order volumes.

The fix was intentionally narrow and infrastructure-safe:

- registered the missing REST route for unread notification counts;
- replaced the repeated per-slot chart queries with batch GROUP BY aggregation;
- preserved all business logic and output structure.

## Verified findings

### 1) Missing polling endpoint (verified production issue)

Evidence:
- Frontend script in [assets/js/notifications-poll.js](assets/js/notifications-poll.js) calls `/mkv/v1/notifications/unread-count`.
- No corresponding REST route existed in the plugin.
- This caused repeated failed polling requests and unnecessary server churn.

Fix:
- Added the REST route and response payload in [includes/controllers/class-notifications.php](includes/controllers/class-notifications.php).

Impact:
- Removes unnecessary failed API calls and makes polling reliable.
- Keeps unread badge calculation consistent with the system’s current notification state.

### 2) Dashboard revenue chart performed repeated single-bucket queries (verified load issue)

Evidence:
- The legacy logic inside [includes/models/class-analytics-service.php](includes/models/class-analytics-service.php) executed one SQL query per time bucket for hourly/daily charts.
- On larger datasets, this multiplies query count exponentially across chart windows.

Fix:
- Replaced the repeated per-bucket logic with grouped aggregation using a single SQL query per chart window and a fast in-memory map.

Impact:
- Reduces chart rendering from many DB round-trips to a small number of aggregate queries.
- Preserves chart labels and values as before.

## Additional audit notes

### SQL and schema posture

The database layer already includes a meaningful set of indexes for active access paths, including:

- order status/time queries;
- cashbook type/time queries;
- audit log event + time filters;
- inventory stock uniqueness and product/location lookups;
- notification read state and timestamps.

This is a good baseline and does not require destructive schema changes.

### Query patterns that were reviewed but not changed

These were inspected and left alone because they were not verified as bottlenecks or because altering them risked business regressions:

- customer order history queries in [includes/controllers/class-customers.php](includes/controllers/class-customers.php);
- AI history aggregation in [includes/controllers/class-dashboard.php](includes/controllers/class-dashboard.php);
- notification page queries in [includes/controllers/class-notifications.php](includes/controllers/class-notifications.php);
- order list paginated queries in [includes/controllers/class-orders.php](includes/controllers/class-orders.php).

These are reasonable for the plugin’s current volume and are bounded by page limits and filters.

### Caching and runtime safety

- Safe caching is already used in some places, such as unread notification counts in the notifications controller.
- No broad caching layer was introduced because the plugin is not yet large enough to justify a general-purpose rewrite.
- No architecture rewrite was undertaken.

## Regression and validation performed

### Syntax validation

Fresh syntax checks were run on the edited files using the local PHP runtime:

- [includes/controllers/class-notifications.php](includes/controllers/class-notifications.php)
- [includes/models/class-analytics-service.php](includes/models/class-analytics-service.php)
- [mini-kiotviet.php](mini-kiotviet.php)
- [includes/models/class-db-schema.php](includes/models/class-db-schema.php)

### Build validation

The project CSS build was also checked with the existing toolchain:

- npm build for Tailwind CSS

This was kept within the current plugin scope and did not introduce unrelated changes.

## Residue cleanup

- No dead code or unfinished Phase 6 scaffolding was added.
- No destructive DB migrations were run.
- No business logic branches were changed outside the verified bottlenecks.

## Conclusion

The system is now materially healthier for production-like dataset use because the verified bottlenecks were removed without introducing unnecessary refactoring or schema churn. Phase 5 was completed, and Phase 6 was not started.

## Recommended next step

If the business later grows into a larger real-world dataset, the next safe optimization layer would be:

1. adding targeted composite indexes for common report filters;
2. caching aggregate dashboard stats for a short TTL;
3. batching big customer/product list queries; and
4. adding measurement logs for slow queries.

These remain future-oriented and were intentionally not done prematurely because the current request explicitly limited work to Phase 5 only.
