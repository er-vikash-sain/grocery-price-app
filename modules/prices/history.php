<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/footer.php';

$db = get_db();

$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;

if ($productId > 0) {
    $productStmt = $db->prepare('SELECT id, name FROM products WHERE id = :id');
    $productStmt->execute(['id' => $productId]);
    $product = $productStmt->fetch();

    if (!$product) {
        $productId = 0;
        $product = null;
    }
} else {
    $product = null;
}

if ($productId > 0) {
    $stmt = $db->prepare(
        'SELECT p.*, s.name AS store_name
         FROM prices p
         JOIN stores s ON s.id = p.store_id
         WHERE p.product_id = :product_id
         ORDER BY p.created_at DESC'
    );
    $stmt->execute(['product_id' => $productId]);
} else {
    $stmt = $db->query(
        'SELECT p.*, s.name AS store_name, pr.name AS product_name
         FROM prices p
         JOIN stores s ON s.id = p.store_id
         JOIN products pr ON pr.id = p.product_id
         ORDER BY p.created_at DESC
         LIMIT 100'
    );
}

$rows = $stmt->fetchAll();

render_header('Price History - Price Tracker', 'prices');

?>

<style>
    .glass-card {
        background-color: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }
    
    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 8px;
    }
    
    .page-subtitle {
        color: var(--text-muted);
        font-size: 15px;
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modern-table th {
        text-align: left;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .modern-table td {
        padding: 16px;
        font-size: 15px;
        color: var(--text-main);
        border-bottom: 1px solid rgba(0,0,0,0.03);
    }

    .modern-table tr:last-child td {
        border-bottom: none;
    }

    .price-tag {
        font-weight: 600;
        color: #FF7E5F;
    }
</style>

<div class="space-y-6">
    <div class="glass-card">
        <div class="mb-6">
            <?php if ($productId > 0 && $product !== null): ?>
                <h2 class="page-title">
                    Price History for <span class="text-[#FF7E5F]"><?php echo sanitize($product['name']); ?></span>
                </h2>
                <a href="/modules/products/view.php?id=<?php echo $productId; ?>" class="text-blue-600 hover:underline text-sm font-medium">← Back to Product Details</a>
            <?php else: ?>
                <h2 class="page-title">Global Price History</h2>
                <p class="page-subtitle">
                    Showing latest price entries across all products (limit 100).
                </p>
            <?php endif; ?>
        </div>

        <?php if (count($rows) === 0): ?>
            <p class="text-gray-500 text-sm">No price history found.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="modern-table">
                    <thead>
                    <tr>
                        <?php if ($productId === 0): ?>
                            <th>Product</th>
                        <?php endif; ?>
                        <th>Store</th>
                        <th>Selling Price</th>
                        <th>MRP</th>
                        <th>Comments</th>
                        <th>Recorded At</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php if ($productId === 0): ?>
                                <td>
                                    <a href="/modules/products/view.php?id=<?php echo (int) $row['product_id']; ?>"
                                       class="font-semibold text-gray-800 hover:text-[#FF7E5F] transition-colors">
                                        <?php echo sanitize((string) $row['product_name']); ?>
                                    </a>
                                </td>
                            <?php endif; ?>
                            <td>
                                <a href="/modules/stores/view.php?id=<?php echo (int) $row['store_id']; ?>"
                                   class="text-gray-800 hover:text-blue-600 transition-colors">
                                    <?php echo sanitize((string) $row['store_name']); ?>
                                </a>
                            </td>
                            <td class="price-tag">
                                ₹<?php echo number_format((float) $row['selling_price'], 2); ?>
                            </td>
                            <td class="text-gray-500">
                                <?php if (!empty($row['mrp'])): ?>
                                    ₹<?php echo number_format((float) $row['mrp'], 2); ?>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-gray-500 italic text-sm">
                                <?php echo sanitize((string) $row['comments']); ?>
                            </td>
                            <td class="text-gray-500 text-sm">
                                <?php echo sanitize((string) $row['created_at']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
render_footer();
