Titre: feat(migration): migrer result.php et detail-restaurant-2.php vers DatabasePDO

Description:
- Migrated `result.php` to use `classes/DatabasePDO.php` for main queries (count and results) and to provide parameterized queries.
- Added `tests/test_result.php` to verify page rendering and main search parameters.
- Will migrate `detail-restaurant-2.php` in the next step (scaffold prepared).

Checklist:
- [x] php -l on modified files
- [x] Added/updated tests (`tests/test_result.php`)
- [x] All tests pass locally (`php tests/*.php`)
- [ ] Create PR in GitHub (branch `feat/migrate-result-detail` -> `main`) and request review

Notes:
- Replaced manual escaping with parameterized queries for `radius`, `type`, `price` when present. Options column filters are validated with a strict regex prior to being injected into SQL (column names cannot be parameterized).
- The map data is retrieved as a separate query without LIMIT (used `preg_replace` to remove LIMIT clause).
- Kept fallback behavior for no results and preserved client-side data injection in `allRestaurantsForMap` variable.
- A non-blocking PHP notice about `$_SERVER['REQUEST_URI']` may appear when testing the page via CLI; this is harmless for the migration but can be fixed by guarding its usage.

Testing performed:
- `php -l` on updated files: OK
- `php tests/test_result.php`: OK
- Full test suite: all tests pass locally

Recommandations:
- Open the PR and run CI (if configured) to get cross-environment validation.
- After merging, proceed to migrate `detail-restaurant-2.php` and add `tests/test_detail.php`.
