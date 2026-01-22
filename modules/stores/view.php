<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/footer.php';

$db = get_db();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    redirect('/modules/stores/list.php');
}

$stmt = $db->prepare('SELECT * FROM stores WHERE id = :id AND is_active = 1');
$stmt->execute(['id' => $id]);
$store = $stmt->fetch();

if (!$store) {
    redirect('/modules/stores/list.php');
}

// Latest prices for this store per product.
$latestStmt = $db->prepare(
    'SELECT p1.*, pr.name AS product_name
     FROM prices p1
     INNER JOIN (
       SELECT product_id, MAX(created_at) AS max_date
       FROM prices
       WHERE store_id = :store_id
       GROUP BY product_id
     ) p2 ON p1.product_id = p2.product_id AND p1.created_at = p2.max_date
     JOIN products pr ON pr.id = p1.product_id
     WHERE p1.store_id = :store_id
     ORDER BY pr.name ASC'
);
$latestStmt->execute(['store_id' => $id]);
$latestPrices = $latestStmt->fetchAll();

// Recent price history for this store (limited).
$historyStmt = $db->prepare(
    'SELECT p.*, pr.name AS product_name
     FROM prices p
     JOIN products pr ON pr.id = p.product_id
     WHERE p.store_id = :store_id
     ORDER BY p.created_at DESC
     LIMIT 50'
);
$historyStmt->execute(['store_id' => $id]);
$history = $historyStmt->fetchAll();

render_header(sanitize($store['name']) . ' - Store Details', 'stores');

?>

<style>
    .store-header-card {
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04);
        padding: 40px;
        display: flex;
        gap: 32px;
        align-items: flex-start;
        position: relative;
        overflow: hidden;
    }

    .store-icon-large {
        width: 120px;
        height: 120px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(255, 179, 71, 0.2) 0%, rgba(255, 126, 95, 0.2) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #FF7E5F;
    }

    .header-text-group {
        flex: 1;
        z-index: 1;
    }

    .header-text-group h1 {
        font-size: 32px;
        font-weight: 700;
        color: var(--text-main);
        margin: 0 0 12px 0;
        letter-spacing: -1px;
    }

    .store-meta {
        display: flex;
        flex-direction: column;
        gap: 8px;
        color: var(--text-muted);
        font-size: 15px;
    }

    .store-meta div {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .glass-card {
        background-color: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }
    
    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    
    .btn-add {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #FF7E5F 0%, #FFB347 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
    }
    
    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 126, 95, 0.3);
    }
</style>

<div class="space-y-8">
    <!-- Store Header Card -->
    <div class="store-header-card">
        <div class="store-icon-large">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"></path>
                <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"></path>
                <path d="M2 7h20"></path>
                <path d="M22 7v3a2 2 0 0 1-2 2v0a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12v0a2 2 0 0 1-2-2V7"></path>
            </svg>
        </div>
        <div class="header-text-group">
            <h1><?php echo sanitize($store['name']); ?></h1>
            <div class="store-meta">
                <?php if (!empty($store['address'])): ?>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?php echo nl2br(sanitize((string) $store['address'])); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($store['city']) || !empty($store['state'])): ?>
                    <div>
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><map-pin class="w-4 h-4" /><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                         <?php 
                            $loc = [];
                            if (!empty($store['city'])) $loc[] = $store['city'];
                            if (!empty($store['state'])) $loc[] = $store['state'];
                            echo sanitize(implode(', ', $loc));
                         ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Current Prices Section -->
    <div class="glass-card">
        <div class="section-title">
            <span>Current Prices in this Store</span>
            <a href="/modules/prices/add.php?store_id=<?php echo $id; ?>" class="btn-add">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add Price
            </a>
        </div>
        
        <?php if (count($latestPrices) === 0): ?>
            <p class="text-gray-500 text-sm">No prices recorded yet for this store.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="modern-table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Selling Price</th>
                        <th>MRP</th>
                        <th>Last Updated</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($latestPrices as $row): ?>
                        <tr>
                            <td>
                                <a href="/modules/products/view.php?id=<?php echo (int) $row['product_id']; ?>"
                                   class="font-semibold text-gray-800 hover:text-[#FF7E5F] transition-colors">
                                    <?php echo sanitize((string) $row['product_name']); ?>
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

    <!-- Recent History Section -->
    <div class="glass-card">
        <div class="section-title">
            <span>Recent Price History</span>
        </div>
        
        <?php if (count($history) === 0): ?>
            <p class="text-gray-500 text-sm">No historical price data yet.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="modern-table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Selling Price</th>
                        <th>MRP</th>
                        <th>Comments</th>
                        <th>Recorded At</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td>
                                <a href="/modules/products/view.php?id=<?php echo (int) $row['product_id']; ?>"
                                   class="font-semibold text-gray-800 hover:text-[#FF7E5F] transition-colors">
                                    <?php echo sanitize((string) $row['product_name']); ?>
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
