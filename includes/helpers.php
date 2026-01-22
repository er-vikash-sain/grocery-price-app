<?php

declare(strict_types=1);

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    $token = csrf_token();

    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token']) || $token === null) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Fetch latest price entries per store for a given product.
 */
function get_latest_prices_for_product(PDO $db, int $productId): array
{
    $sql = <<<SQL
SELECT p1.*
FROM prices p1
INNER JOIN (
  SELECT store_id, MAX(created_at) AS max_date
  FROM prices
  WHERE product_id = :product_id
  GROUP BY store_id
) p2
ON p1.store_id = p2.store_id AND p1.created_at = p2.max_date
WHERE p1.product_id = :product_id
SQL;

    $stmt = $db->prepare($sql);
    $stmt->execute(['product_id' => $productId]);

    return $stmt->fetchAll();
}

/**
 * Get the cheapest store for a given product (based on latest prices).
 */
function get_cheapest_store_for_product(PDO $db, int $productId): ?array
{
    $sql = <<<SQL
SELECT p.*, s.name AS store_name
FROM (
  SELECT p1.*
  FROM prices p1
  INNER JOIN (
    SELECT store_id, MAX(created_at) AS max_date
    FROM prices
    WHERE product_id = :product_id
    GROUP BY store_id
  ) p2
  ON p1.store_id = p2.store_id AND p1.created_at = p2.max_date
  WHERE p1.product_id = :product_id
) AS p
JOIN stores s ON s.id = p.store_id
ORDER BY p.selling_price ASC
LIMIT 1
SQL;

    $stmt = $db->prepare($sql);
    $stmt->execute(['product_id' => $productId]);
    $row = $stmt->fetch();

    return $row !== false ? $row : null;
}

