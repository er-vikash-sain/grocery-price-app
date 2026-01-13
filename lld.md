export HOMEBREW_PREFIX="/opt/homebrew";
export HOMEBREW_CELLAR="/opt/homebrew/Cellar";
export HOMEBREW_REPOSITORY="/opt/homebrew";
fpath[1,0]="/opt/homebrew/share/zsh/site-functions";
eval "$(/usr/bin/env PATH_HELPER_ROOT="/opt/homebrew" /usr/libexec/path_helper -s)";
[ -z "${MANPATH-}" ] || export MANPATH=":${MANPATH#:}";
export INFOPATH="/opt/homebrew/share/info:${INFOPATH:-}";

# Low-Level Design: Local & Online Price Tracker

This document translates the HLD in `plan.md.md` into a concrete low-level design, including **user authentication (login, logout) and password recovery (forgot/reset password)**.

Tech stack: **PHP (no framework) + MySQL + HTML + TailwindCSS**.

---

## 1. Database Design (Detailed)

### 1.1 Existing Tables (Refined)

#### `stores`

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name` VARCHAR(150) NOT NULL
- `address` TEXT NULL
- `city` VARCHAR(100) NULL
- `state` VARCHAR(100) NULL
- `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- Index: `idx_stores_name` ON (`name`)

#### `products`

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name` VARCHAR(150) NOT NULL
- `description` TEXT NULL
- `image` VARCHAR(255) NULL
- `tags` VARCHAR(255) NULL
- `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- Index: `idx_products_name` ON (`name`)

#### `prices`

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `product_id` INT UNSIGNED NOT NULL
- `store_id` INT UNSIGNED NOT NULL
- `price` DECIMAL(10,2) NOT NULL
- `unit` VARCHAR(20) NOT NULL
- `comments` VARCHAR(255) NULL
- `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- FK: `product_id` → `products.id` (ON DELETE CASCADE)
- FK: `store_id` → `stores.id` (ON DELETE CASCADE)
- Index: `idx_prices_product` ON (`product_id`)
- Index: `idx_prices_store` ON (`store_id`)

### 1.2 New Tables for User Authentication

#### `users`

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name` VARCHAR(150) NOT NULL
- `email` VARCHAR(150) NOT NULL UNIQUE
- `password_hash` VARCHAR(255) NOT NULL
- `is_active` TINYINT(1) NOT NULL DEFAULT 1
- `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- Index: `idx_users_email` ON (`email`)

Usage:
- Stores auth users who can log into the app and manage stores/products/prices.
- Passwords always stored as hash (e.g., PHP `password_hash` with `PASSWORD_BCRYPT`).

#### `password_resets`

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id` INT UNSIGNED NOT NULL
- `token` CHAR(64) NOT NULL UNIQUE   <!-- hex-encoded -->
- `expires_at` DATETIME NOT NULL
- `used_at` DATETIME NULL
- `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- FK: `user_id` → `users.id` (ON DELETE CASCADE)
- Index: `idx_password_resets_user` ON (`user_id`)

Usage:
- Stores one-time tokens for **forgot password / reset password** flow.
- `token` is a random 32-byte value hex-encoded.
- A token is considered **invalid** if:
  - `NOW() > expires_at`, or
  - `used_at IS NOT NULL`.

---

## 2. Application Structure (Concrete Files)

Base folder structure builds on the HLD, adding an `auth` module and shared helpers:

```
/price-tracker
│
├── /config
│   └── database.php
│
├── /public
│   ├── index.php
│   ├── login.php           # entry for login form
│   ├── forgot-password.php # entry for forgot password form
│   ├── reset-password.php  # entry for reset form (with token)
│   ├── logout.php          # logout endpoint
│   └── /assets
│       ├── /css
│       └── /images
│
├── /modules
│   ├── /auth
│   │   ├── LoginController.php
│   │   ├── ForgotPasswordController.php
│   │   ├── ResetPasswordController.php
│   │   └── AuthMiddleware.php     # simple auth guard functions
│   │
│   ├── /stores
│   │   ├── add.php
│   │   ├── list.php
│   │   └── view.php
│   │
│   ├── /products
│   │   ├── add.php
│   │   ├── list.php
│   │   └── view.php
│   │
│   └── /prices
│       ├── add.php
│       └── history.php
│
├── /includes
│   ├── header.php
│   ├── footer.php
│   ├── helpers.php
│   └── auth.php             # session helpers, guards
│
└── /sql
    ├── schema.sql
    └── seed_users.sql       # optional: admin user seed
```

Notes:
- `public/*.php` files are **entry points** that:
  - start session
  - include `config/database.php`, `includes/auth.php`, `includes/helpers.php`
  - dispatch to appropriate controller or inline logic.
- `modules/auth/*Controller.php` may be used later if you refactor logic out of `public/*.php`; current implementation handles requests inline in the entrypoints.

---

## 3. Common Infrastructure

### 3.1 Database Connection (`config/database.php`)

- Exposes a function `get_db()` that returns a shared `PDO` instance:
  - DSN: `mysql:host=...;dbname=...;charset=utf8mb4`
  - Error mode: `PDO::ERRMODE_EXCEPTION`
  - Emulate prepares: `false`

Pseudo-signature:
- `function get_db(): PDO`

### 3.2 Helpers (`includes/helpers.php`)

Core functions (LLD level):

- `function redirect(string $url): void`
  - Sends `Location` header and `exit`.

- `function csrf_token(): string`
  - Starts session (if not already).
  - Generates and stores token in `$_SESSION['csrf_token']` if missing.
  - Returns the token string.

- `function csrf_field(): string`
  - Returns HTML: `<input type="hidden" name="csrf_token" value="...">`

- `function verify_csrf(string $token): bool`
  - Compares supplied token with `$_SESSION['csrf_token']` using `hash_equals`.

- `function sanitize(string $value): string`
  - Returns `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')` for output.

### 3.3 Auth Helpers (`includes/auth.php`)

Functions:

- `function start_session_if_needed(): void`
  - Calls `session_start()` if `session_status() !== PHP_SESSION_ACTIVE`.

- `function login_user(array $user): void`
  - Stores at minimum:
    - `$_SESSION['user_id'] = $user['id'];`
    - `$_SESSION['user_email'] = $user['email'];`

- `function logout_user(): void`
  - Unsets all auth-related session keys and calls `session_destroy()`.

- `function current_user_id(): ?int`
  - Returns `$_SESSION['user_id']` if set; otherwise `null`.

- `function require_auth(): void`
  - If `current_user_id()` is null, redirects to `login.php?redirect=...`.

Usage:
- All protected pages (`stores/*`, `products/*`, `prices/*`, `public/index.php`) call `require_auth()` at the top.

---

## 4. User Authentication Flows (Detailed)

### 4.1 Login Flow

**Entry point:** `public/login.php`  
**Controller:** `modules/auth/LoginController.php`

#### 4.1.1 GET /login.php

- Behavior:
  - If user already logged in (`current_user_id()` not null):
    - Redirect to `index.php`.
  - Otherwise:
    - Render login form.

- Form fields:
  - `email` (type=email, required)
  - `password` (type=password, required)
  - Hidden `csrf_token`

- UI (Tailwind):
  - Centered card with:
    - App title
    - Email + password fields
    - "Login" button
    - Link to `forgot-password.php`

#### 4.1.2 POST /login.php

- Steps:
  1. Call `verify_csrf($_POST['csrf_token'] ?? '')`; if false, show error "Invalid request".
  2. Normalize input:
     - `$email = trim(strtolower($_POST['email'] ?? ''));`
     - `$password = $_POST['password'] ?? '';`
  3. Basic validation:
     - If email or password empty → show error "Email and password are required".
  4. DB lookup:
     - `SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1;`
  5. If no user found:
     - Show generic error "Invalid email or password".
  6. Verify password:
     - `password_verify($password, $user['password_hash'])`
     - If false → same generic error.
  7. On success:
     - Call `login_user($user)`.
     - If `$_GET['redirect']` present → redirect there; else redirect to `index.php`.

### 4.2 Logout Flow

**Entry point:** `public/logout.php`

- Steps:
  1. `start_session_if_needed()`
  2. `logout_user()`
  3. Redirect to `login.php`

No form/CSRF required if triggered by POST from a button; for extra safety, you may enforce POST+CSRF.

### 4.3 Forgot Password Flow

**Entry point:** `public/forgot-password.php`  
**Controller:** `modules/auth/ForgotPasswordController.php`

#### 4.3.1 GET /forgot-password.php

- If user is logged in:
  - Optionally redirect to `index.php`.
- Otherwise:
  - Render form asking only for `email`.

- Form fields:
  - `email` (type=email, required)
  - Hidden `csrf_token`

#### 4.3.2 POST /forgot-password.php

- Steps:
  1. Verify CSRF.
  2. Normalize email, ensure not empty.
  3. Look up user:
     - `SELECT id, email FROM users WHERE email = :email AND is_active = 1 LIMIT 1;`
  4. If user not found:
     - Do NOT reveal; show generic message:  
       "If this email exists in our system, you will receive a reset link."
  5. If user found:
     - Generate token:
       - `$raw = random_bytes(32);`
       - `$token = bin2hex($raw);` (64-char string)
     - Set expiry:
       - `$expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour`
     - Insert into `password_resets`:
       - `INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at);`
  6. Build reset link:
     - Build dynamically using host header, e.g. `http(s)://{HTTP_HOST}/reset-password.php?token=...`.
  7. For now (no email infra), show reset link on-screen for development or log it.

- UI:
  - After POST, always display:
    - "If this email exists in our system, you will receive a reset link."

### 4.4 Reset Password Flow

**Entry point:** `public/reset-password.php`  
**Controller:** `modules/auth/ResetPasswordController.php`

#### 4.4.1 GET /reset-password.php?token=...

- Steps:
  1. Read `$_GET['token']`.
  2. If missing/empty → show error "Invalid or expired link".
  3. Lookup token:
     - `SELECT pr.*, u.email FROM password_resets pr JOIN users u ON u.id = pr.user_id WHERE pr.token = :token LIMIT 1;`
  4. Validate:
     - If no row → show "Invalid or expired link".
     - If `NOW() > expires_at` → show "This link has expired."
     - If `used_at IS NOT NULL` → show "This link has already been used."
  5. If valid:
     - Render reset form.

- Form fields:
  - `password` (type=password, required)
  - `password_confirm` (type=password, required)
  - Hidden `token` (exact from URL)
  - Hidden `csrf_token`

#### 4.4.2 POST /reset-password.php

- Steps:
  1. Verify CSRF.
  2. Read:
     - `$token`, `$password`, `$password_confirm`.
  3. Validate:
     - Non-empty passwords.
     - Length ≥ 8 chars (for start).
     - `$password === $password_confirm`.
  4. Lookup token and user (same as GET).
  5. Validate token again (expiry, used_at).
  6. Hash new password:
     - `$hash = password_hash($password, PASSWORD_BCRYPT);`
  7. Update `users`:
     - `UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :user_id;`
  8. Mark reset token as used:
     - `UPDATE password_resets SET used_at = NOW() WHERE id = :id;`
  9. Optionally auto-login the user:
     - Fetch user, call `login_user($user)`, redirect to `index.php` with success message.
     - Or redirect to `login.php?reset=success`.

---

## 5. User Registration (Optional but Recommended)

If you want web-based registration (instead of manual seeding):

**Entry point:** `public/register.php`  
**Controller:** `modules/auth/RegisterController.php`

- Fields:
  - `name`, `email`, `password`, `password_confirm`, `csrf_token`.
- Steps:
  1. Validate CSRF.
  2. Validate inputs:
     - Required fields
     - Email format
     - Password length & confirmation
  3. Ensure email not already taken:
     - `SELECT id FROM users WHERE email = :email LIMIT 1;`
  4. Hash password & insert user:
     - `INSERT INTO users (name, email, password_hash) VALUES (...);`
  5. Login user or redirect to login page.

If you prefer manual control, skip this and use `sql/seed_users.sql` to insert admin.

---

## 6. Protecting Existing Modules with Auth

Each protected page should:

1. Start session & include auth:
   - `require_once __DIR__ . '/../includes/auth.php';`
   - `start_session_if_needed();`
2. Enforce auth:
   - `require_auth();`

Pages to protect:
- `public/index.php`
- `modules/stores/*.php`
- `modules/products/*.php`
- `modules/prices/*.php`

Effect:
- Anonymous users always redirected to `login.php`.

---

## 7. Price Logic Implementation (Code-Level)

### 7.1 Fetch Latest Prices per Store for a Product

Function (e.g., in `includes/helpers.php` or `modules/prices/helpers.php`):

- `function get_latest_prices_for_product(PDO $db, int $product_id): array`

Logic:

```sql
SELECT p1.*
FROM prices p1
INNER JOIN (
  SELECT store_id, MAX(created_at) AS max_date
  FROM prices
  WHERE product_id = :product_id
  GROUP BY store_id
) p2
ON p1.store_id = p2.store_id AND p1.created_at = p2.max_date
WHERE p1.product_id = :product_id;
```

Return:
- Array of rows with `store_id`, `price`, `unit`, `created_at`, etc.

### 7.2 Determine Cheapest Store

Function:

- `function get_cheapest_store_for_product(PDO $db, int $product_id): ?array`

Steps:
- Call `get_latest_prices_for_product`.
- If no rows → return `null`.
- Sort rows by `price` ascending (SQL `ORDER BY price ASC` or PHP `usort`).
- Return first element, optionally with joined store name:

```sql
SELECT p.*, s.name AS store_name
FROM ( ... latest price query ... ) p
JOIN stores s ON s.id = p.store_id
ORDER BY p.price ASC
LIMIT 1;
```

Usage on product detail page:
- Display table with all prices.
- Highlight row where `store_id == cheapest['store_id']`.
- Display "Recommended Store: {store_name} – {price} / {unit}".

---

## 8. UI Wiring Summary

- `public/login.php`
  - GET: show login form.
  - POST: authenticate and redirect.

- `public/forgot-password.php`
  - GET: email form.
  - POST: generate reset token, show confirmation.

- `public/reset-password.php`
  - GET: validate token, show password fields.
  - POST: validate token, save new password, redirect.

- `public/logout.php`
  - GET/POST: clear session, redirect to login.

- Existing CRUD pages:
  - Call `require_auth()` to enforce login.
  - Use `current_user_id()` if per-user data is later required.

This LLD should be directly implementable with small, focused PHP files and minimal additional configuration on top of your current plan.
