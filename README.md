# Grocery Price Tracker

Simple web app to track product prices across multiple local and online stores and quickly find the cheapest option before purchase.

Stack: **PHP (no framework) + MySQL + HTML + TailwindCSS**.

## Features

- Store management (local + online stores)
- Product management (shared products across stores)
- Price tracking per store with history
- Cheapest store recommendation per product
- User authentication:
  - Login / logout
  - Forgot password (reset link)
  - Reset password

## Project Structure

Key folders:

- `config/` – database connection (`database.php`)
- `includes/` – shared helpers (`helpers.php`, `auth.php`)
- `public/` – web entrypoints (dashboard, auth pages, assets)
- `modules/` – feature modules (`stores`, `products`, `prices`, `auth`)
- `sql/` – database schema and (optional) seed files

## Setup

1. **Create database**

   ```sql
   CREATE DATABASE price_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Apply schema**

   ```bash
   mysql -u your_user -p price_tracker < sql/schema.sql
   ```

3. **Configure DB connection**

   Set environment variables (or use defaults):

   - `DB_HOST` (default `127.0.0.1`)
   - `DB_NAME` (default `price_tracker`)
   - `DB_USER` (default `root`)
   - `DB_PASS` (default empty)

4. **Create an initial user**

   In a PHP shell or small script:

   ```php
   <?php
   $hash = password_hash('your-password', PASSWORD_BCRYPT);
   echo $hash;
   ```

   Then insert into MySQL:

   ```sql
   INSERT INTO users (name, email, password_hash) 
   VALUES ('Admin', 'admin@example.com', 'PASTE_HASH_HERE');
   ```

5. **Run the app (dev)**

   From the project root:

   ```bash
   php -S localhost:8000 -t public
   ```

   Open `http://localhost:8000/login.php` in your browser.

## Auth Flows (Summary)

- **Login**: `public/login.php` – email + password, CSRF-protected.
- **Forgot password**: `public/forgot-password.php` – generates token in `password_resets` and displays a reset link (for development).
- **Reset password**: `public/reset-password.php?token=...` – validates token, sets new password, and marks token as used.
- **Logout**: `public/logout.php` – clears session and redirects to login.

## Development Notes

- All protected pages should call `require_auth()` from `includes/auth.php`.
- Business and DB details are documented in `plan.md.md` (HLD) and `lld.md` (LLD).

