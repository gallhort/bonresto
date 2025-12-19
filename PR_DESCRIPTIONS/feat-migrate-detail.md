Titre: feat(migration): migrer detail-restaurant-2.php vers DatabasePDO

Description:
- Migrated `detail-restaurant-2.php` to use `classes/DatabasePDO.php` for queries that previously used inline SQL via `$dbh->query` or `$conn->query`.
- Sanitized and normalized `$_GET['nom']` input and replaced vulnerable inline concatenations with parameterized queries using the PDO wrapper.
- Replaced the following raw queries:
  - Aggregation stats from `comments` -> parameterized fetch
  - Main restaurant data (`v.*, o.*, p.*`) -> parameterized fetch
  - `regime` and `options` blocks -> parameterized fetchAll
- Added `tests/test_detail.php` to validate rendering (basic smoke test).

Checklist:
- [x] php -l on modified files
- [x] Added `tests/test_detail.php`
- [x] All tests pass locally (`php tests/*.php`)
- [ ] Create PR on GitHub (branch `feat/migrate-detail` -> `main`) and request review

Notes:
- The test runs via CLI and reports some non-fatal PHP warnings when database returns no rows; these are non-blocking and expected in isolated test environments. Consider adding more robust null-checking in templates to suppress warnings in CLI mode.
- Next: open PR and address any review comments; consider migrating other pages with inline queries.
