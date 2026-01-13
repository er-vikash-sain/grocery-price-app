# Implementation Gaps vs `plan.md`

This document compares the current codebase to the project plan in `plan.md` and lists what is missing or incomplete.

## 1. Overall Status

- **Aligned**: Database schema, core price logic helpers, basic folder structure, Tailwind-based UI shell, and user authentication.
- **Partially done**: Dashboard page exists but only as a placeholder; no real data or navigation to feature pages.
- **Missing**: All CRUD flows and screens for stores, products, and prices; search; product detail comparison view; price history UI; shared header/footer includes.

The rest of this file details gaps by area.

## 2. Core Modules

### 2.1 Store Management

**Plan expectations**

- Maintain a list of stores with fields: `id`, `name`, `address`, `city`, `state`, `created_at`, `updated_at`.
- Actions: add store, view store list, edit store, delete (soft delete recommended).
- Screens:
  - Store list page.
  - Add/edit store form.
  - Store detail page showing products that have prices in this store.

**Current implementation**

- Table `stores` exists and matches the planned fields and indexing (`sql/schema.sql`).
- Folder `modules/stores/` exists but is **empty** (no PHP files).
- There are no public routes/endpoints for store list/add/view/edit/delete.

**Gaps**

- No UI or endpoints for:
  - Creating a store.
  - Listing stores (with basic details, pagination/search).
  - Editing store details.
  - Soft-deleting or deactivating stores.
  - Store detail page that lists all products with their latest prices in that store.
- No integration of stores into the dashboard (e.g., total store count).

### 2.2 Product Management

**Plan expectations**

- Maintain products with fields: `id`, `name`, `description`, `image`, `tags`, timestamps.
- Actions: add product, view product list, search by name/tag, view product details.
- Screens:
  - Product list (search enabled).
  - Add/edit product form.
  - Product detail page showing comparison across stores.

**Current implementation**

- Table `products` exists with the expected fields and `idx_products_name` index (`sql/schema.sql`).
- Folder `modules/products/` exists but is **empty**.
- There are no public pages/routes for product listing, creation, editing, or viewing details.
- No handling of product images or tags in the UI.

**Gaps**

- No UI or endpoints for:
  - Creating products (including optional image path and tags).
  - Listing products with basic search by name/tag.
  - Editing product name/description/image/tags.
  - Viewing a product detail page.
- Product detail page is missing:
  - Price comparison table across stores (store name, latest price, unit, last updated).
  - Highlighting of the lowest price and “Recommended Store” indicator.
- No navigation links from dashboard or elsewhere to product pages.

### 2.3 Price Tracking

**Plan expectations**

- Track price per product per store over time with fields: `id`, `product_id`, `store_id`, `price`, `unit`, `comments`, timestamps.
- Rules:
  - Multiple price entries over time per product+store pair.
  - Latest `created_at` is considered current price.
- Screens:
  - Price entry page (select product + store, enter price + unit + comments).
  - Price history view per product (and/or per store).

**Current implementation**

- Table `prices` matches the planned structure, foreign keys, and indexes (`sql/schema.sql`).
- Helper logic in `includes/helpers.php`:
  - `get_latest_prices_for_product(PDO $db, int $productId): array` implements the latest-price-per-store query.
  - `get_cheapest_store_for_product(PDO $db, int $productId): ?array` implements the cheapest-store logic based on latest prices.
- Folder `modules/prices/` exists but is **empty**.
- No public page for entering prices or viewing price history.

**Gaps**

- No UI or endpoints to:
  - Create a new price entry (product + store + price + unit + comments).
  - View price history per product and/or per store (e.g., timeline of price changes).
- Core helper functions are not integrated into any page:
  - Product detail page should call `get_latest_prices_for_product` and `get_cheapest_store_for_product` to render comparison and recommendation.
- No validation rules/UI feedback for price/unit/comments (e.g., non-negative prices).

## 3. Screens & Navigation

### 3.1 Dashboard

**Plan expectations**

- Dashboard should show:
  - Total products.
  - Total stores.
  - Recently updated prices.

**Current implementation**

- `public/index.php`:
  - Requires authentication (`require_auth()`).
  - Renders a Tailwind-based layout with a header and placeholder text: “Dashboard content will go here (totals, recent prices, etc.).”
  - No actual data queries or components yet.

**Gaps**

- No queries to:
  - Count stores.
  - Count products.
  - Fetch recent price updates (e.g., latest N entries from `prices`).
- No UI components showing:
  - Summary cards for total products/stores.
  - Table or list of recent price updates.
- No dashboard navigation links to:
  - Store management.
  - Product list.
  - Price entry/history.

### 3.2 Store Pages

**Plan expectations**

- Store list, add store form, and store detail page with products priced in that store.

**Current implementation**

- No `modules/stores/*.php` or `public/stores*.php` implementing these pages.

**Gaps**

- All store pages are missing:
  - URL structure (e.g., `/stores`, `/stores/add`, `/stores/view.php?id=...`).
  - Associated PHP controllers and views.
  - Tailwind-based UI for forms and lists.

### 3.3 Product Pages

**Plan expectations**

- Product list, add product form, search, and product detail comparison view with cheapest store highlighted.

**Current implementation**

- No product-related public pages or module PHP files exist.

**Gaps**

- All product pages are missing:
  - Product list page with search (by name/tag).
  - Add/edit product forms.
  - Product detail page showing:
    - Product info (image, name, description).
    - Price comparison table (store, latest price, unit, last updated).
    - Recommended store (based on cheapest current price).

### 3.4 Price Entry & History

**Plan expectations**

- Dedicated price entry screen and price history screen.

**Current implementation**

- No price entry or history pages implemented.

**Gaps**

- Need pages for:
  - Entering a new price row (product + store + price details).
  - Viewing historical prices for a given product (optionally per store).
- Optional enhancements around history (charts, visualizations) are not started.

## 4. Shared Layout & Includes

**Plan expectations**

- `includes/header.php` and `includes/footer.php` for shared layout.
- Reuse across pages (dashboard, auth pages, module pages).

**Current implementation**

- `includes/helpers.php` and `includes/auth.php` exist and are used.
- Layout HTML is duplicated per page (`public/index.php`, `public/login.php`, `public/forgot-password.php`, `public/reset-password.php`).
- There is no `includes/header.php` or `includes/footer.php`.

**Gaps**

- No shared header/footer include files.
- No central navigation menu (e.g., links to dashboard, products, stores, price entry).
- Reusable layout components are not yet extracted, which will make future module pages more repetitive.

## 5. Search Functionality

**Plan expectations**

- Search products by name/tag.

**Current implementation**

- No search-related query helpers or UI.
- No search input on any existing page.

**Gaps**

- Missing:
  - SQL queries for searching products by name or tags (e.g., `WHERE name LIKE ... OR tags LIKE ...`).
  - UI for entering search terms and displaying filtered results on the product list page.

## 6. Authentication & Users

**Plan expectations**

- User accounts are listed under “Phase 3 (Optional Enhancements)” as a future scalability item.

**Current implementation**

- Implemented features:
  - `users` table and `password_resets` table in `sql/schema.sql`.
  - `includes/auth.php` with session handling helpers (`login_user`, `logout_user`, `current_user_id`, `require_auth`).
  - `public/login.php`, `public/logout.php`, `public/forgot-password.php`, `public/reset-password.php`:
    - Tailwind-based forms.
    - CSRF protection (using helpers).
    - Basic password reset flow storing tokens in DB.

**Note**

- This is **ahead of the plan**, not a gap. It aligns with the “Add users (personal price tracking)” direction, but currently there is:
  - No UI for user registration or user management.
  - No per-user scoping of data (all stores/products/prices are global).

## 7. Optional Enhancements (Not Yet Implemented)

The plan marks the following as Phase 3 / future items. Their absence is expected at MVP stage but worth tracking:

- **Price history chart**
  - No charting library integration.
  - No endpoint or data API to feed historical price data into charts.
- **Alerts (price drop)**
  - No background jobs or triggers to detect price drops.
  - No notification mechanism (email, UI alerts, etc.).
- **User accounts / personalization**
  - Authentication exists, but:
    - Prices, stores, and products are not scoped per user.
    - No concept of “owner” or “shared lists”.
- **Categories**
  - No `categories` table or category field on products.
  - No category-based filtering/browsing UI.
- **Export (CSV)**
  - No endpoints or buttons to export products/prices to CSV.

## 8. Suggested Next Steps

To close the most important gaps and reach a usable MVP aligned with `plan.md`, a practical sequence would be:

1. Implement **product module**:
   - Product list + search.
   - Add/edit product forms.
   - Product detail page using existing helper functions to show comparison and cheapest store.
2. Implement **store module**:
   - Store list, add/edit forms, and store detail page.
3. Implement **price entry & history**:
   - Price entry form tied to products/stores.
   - Basic price history table per product.
4. Enhance **dashboard**:
   - Totals and recent price changes.
5. Extract **shared layout includes**:
   - `includes/header.php` / `includes/footer.php` with consistent navigation.

These steps will move the implementation much closer to the original `plan.md` while using the existing schema and helper logic.

