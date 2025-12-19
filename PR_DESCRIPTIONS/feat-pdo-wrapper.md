Title: feat(db): add DatabasePDO wrapper, tests and migrate getresto/gettype

Summary:
- Added `classes/DatabasePDO.php` — minimal PDO wrapper (reuses global `$dbh` if available).
- Added `tests/test_database.php` — in-memory SQLite test validating wrapper basic operations.
- Migrated `getresto.php` and `gettype.php` to use the wrapper and prepared/parameterized queries.
- Updated `README.md` with test instructions and added `SECURITY_AND_REFACTOR.md` notes.
- Added `dev-tools/php-lint-report.ps1` script for easier local linting.

Why:
Centralizing DB access improves security (easier prepared statements), code reuse and testing.

How to test locally:
1. Ensure `.env` is configured or use local dev defaults.
2. Run: `php tests/test_database.php` → should output `OK: DatabasePDO basic test passed`.
3. Smoke-test endpoints:
   - `gettype.php` (open in browser) → returns JSON list of types.
   - POST to `getresto.php` with `nom` → returns restaurant JSON.
4. Run lint script: `.\dev-tools\php-lint-report.ps1` (PowerShell).

Notes:
- This PR intentionally keeps changes small and limited to 2 endpoints to ease review.
- Next steps: migrate more endpoints to the wrapper in small follow-up PRs.
