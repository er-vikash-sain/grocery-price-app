# Releases

## v0.1.0 – Initial Planning & Auth Scaffold

Date: YYYY-MM-DD

### Added

- High-level design in `plan.md.md` outlining:
  - Store, product, and price tracking model
  - Core screens and comparison logic
  - Initial folder structure
- Low-level design in `lld.md` covering:
  - Detailed MySQL schema
  - `users` and `password_resets` tables
  - Auth flows (login, logout, forgot/reset password)
  - Helper and module structure
- Database schema file `sql/schema.sql` with:
  - `stores`, `products`, `prices`
  - `users`, `password_resets`
- PHP scaffolding:
  - `config/database.php` with `get_db()` using env vars
  - `includes/helpers.php` (redirect, CSRF, sanitize)
  - `includes/auth.php` (session helpers and `require_auth()`)
  - `public/index.php` basic dashboard (protected)
  - `public/login.php`, `public/forgot-password.php`, `public/reset-password.php`, `public/logout.php`

### Notes

- No public registration yet; create users directly in the database.
- Reset links are shown in the UI for development instead of sending emails.

