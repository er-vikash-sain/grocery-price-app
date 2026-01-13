Below is a **clean, build-ready project plan** based strictly on your requirements, tech stack, and real-world use case. This is written as if you are about to start development tomorrow.

---

# Project Plan: Local & Online Price Tracker

## 1. Project Overview

**Purpose**
Track product prices across multiple stores (local + online) to identify the **best available price before purchase**.

**Core Use Case (Confirmed)**

* Same product exists across multiple stores
* Only **price varies**, not the product
* User can:

  * Add/update prices per store
  * View latest prices
  * See recommended (cheapest) option

This is **not an e-commerce system**.
This is a **decision-support / comparison tool**.

---

## 2. Core Modules (Confirmed + Refined)

### Module 1: Store Management

**Goal:** Maintain a list of physical and online stores.

**Fields**

* id (PK)
* name
* address
* city
* state
* created_at
* updated_at

**Actions**

* Add store
* View store list
* Edit store
* Delete store (soft delete recommended)

---

### Module 2: Product Management

**Goal:** Maintain a single product entity shared across stores.

**Fields**

* id (PK)
* name
* description
* image (path)
* tags (comma-separated or JSON)
* created_at
* updated_at

**Actions**

* Add product
* View product list
* Search product (by name / tag)
* View product details

> Important rule:
> **Same product = same product ID**
> Amazon / local shop prices go into the price table, not a new product.

---

### Module 3: Price Tracking (Core Logic)

**Goal:** Track price per product per store over time.

**Fields**

* id (PK)
* product_id (FK)
* store_id (FK)
* price
* unit (kg, gm, litre, ml, pcs, etc.)
* comments (optional)
* created_at
* updated_at

**Rules**

* One store can have multiple price updates over time
* Latest `created_at` = current price
* History preserved for future analysis

---

## 3. Recommended Database Schema (MySQL)

### stores

```sql
id INT AUTO_INCREMENT PRIMARY KEY
name VARCHAR(150)
address TEXT
city VARCHAR(100)
state VARCHAR(100)
created_at DATETIME
updated_at DATETIME
```

### products

```sql
id INT AUTO_INCREMENT PRIMARY KEY
name VARCHAR(150)
description TEXT
image VARCHAR(255)
tags VARCHAR(255)
created_at DATETIME
updated_at DATETIME
```

### prices

```sql
id INT AUTO_INCREMENT PRIMARY KEY
product_id INT
store_id INT
price DECIMAL(10,2)
unit VARCHAR(20)
comments VARCHAR(255)
created_at DATETIME
updated_at DATETIME
```

**Indexes (Important for performance)**

* prices(product_id)
* prices(store_id)
* products(name)

---

## 4. Key Screens / Pages (HTML + Tailwind)

### 1. Dashboard

* Total products
* Total stores
* Recently updated prices

---

### 2. Store Pages

* Store list
* Add store form
* Store detail (shows products priced in this store)

---

### 3. Product Pages

* Product list (search enabled)
* Add product form
* Product detail page (MOST IMPORTANT)

**Product Detail View**

* Product info (image, name, description)
* Price comparison table:

  * Store name
  * Latest price
  * Unit
  * Last updated
* Highlight **lowest price**
* “Recommended Store” badge

---

### 4. Price Entry Page

* Select product
* Select store
* Enter price + unit
* Add comments (optional)
* Save (creates new price row)

---

## 5. Core Business Logic (Critical)

### Latest Price Query (Per Store)

```sql
SELECT p1.*
FROM prices p1
INNER JOIN (
  SELECT store_id, MAX(created_at) max_date
  FROM prices
  WHERE product_id = ?
  GROUP BY store_id
) p2
ON p1.store_id = p2.store_id AND p1.created_at = p2.max_date
WHERE p1.product_id = ?
```

### Cheapest Store Logic

* From latest prices
* Sort by price ASC
* First record = recommended store

---

## 6. Folder Structure (PHP + HTML + Tailwind)

```
/price-tracker
│
├── /config
│   └── database.php
│
├── /public
│   ├── index.php
│   ├── /assets
│   │   ├── /css
│   │   └── /images
│
├── /modules
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
│   └── helpers.php
│
└── /sql
    └── schema.sql
```

---

## 7. Phase-Wise Development Plan

### Phase 1 (Week 1): Foundation

* Database schema
* Store CRUD
* Product CRUD
* Basic UI with Tailwind

### Phase 2 (Week 2): Core Value

* Price tracking
* Product detail comparison view
* Cheapest store logic
* Search functionality

### Phase 3 (Optional Enhancements)

* Price history chart
* Alerts (price drop)
* User accounts
* Categories
* Export (CSV)

---

## 8. Design Principles (Important)

* Minimal UI
* Data-first screens
* Mobile-friendly (Tailwind)
* Fast search
* Clear recommendation highlight

---

## 9. Future Scalability (Optional)

* Add users (personal price tracking)
* Share price lists
* Browser extension
* API-first backend
* AI-based price prediction

---

## 10. Next Step (Action Required)

Tell me **what you want next**:

1. Database SQL file
2. Product detail page UI (Tailwind)
3. PHP logic for cheapest store
4. Search implementation
5. Full CRUD code for one module
6. ER diagram
7. MVP checklist

I will proceed **step-by-step**, production-grade, no fluff.
