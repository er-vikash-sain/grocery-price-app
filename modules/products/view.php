<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/footer.php';

$db = get_db();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    redirect('/modules/products/list.php');
}

$stmt = $db->prepare('SELECT * FROM products WHERE id = :id AND is_active = 1');
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    redirect('/modules/products/list.php');
}

// Get all stores with prices for this product
$storesStmt = $db->prepare(
    'SELECT DISTINCT s.id, s.name, s.city, s.state
     FROM stores s
     INNER JOIN prices p ON p.store_id = s.id
     WHERE p.product_id = :product_id AND s.is_active = 1
     ORDER BY s.name ASC'
);
$storesStmt->execute(['product_id' => $id]);
$stores = $storesStmt->fetchAll();

// Get price history for each store
$pricesByStore = [];
foreach ($stores as $store) {
    $pricesStmt = $db->prepare(
        'SELECT selling_price, mrp, created_at, comments
         FROM prices
         WHERE product_id = :product_id AND store_id = :store_id
         ORDER BY created_at DESC
         LIMIT 10'
    );
    $pricesStmt->execute([
        'product_id' => $id,
        'store_id' => $store['id']
    ]);
    $pricesByStore[(int)$store['id']] = $pricesStmt->fetchAll();
}

render_header('Product Details - ' . $product['name'], 'products');

?>

<style>
    .product-header-card {
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
    }

    .product-image-large {
        width: 200px;
        height: 200px;
        border-radius: 16px;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(255, 179, 71, 0.1) 0%, rgba(255, 126, 95, 0.1) 100%);
        border: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .product-image-large img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .header-text-group {
        flex: 1;
    }

    .header-text-group h1 {
        font-size: 32px;
        font-weight: 700;
        color: var(--text-main);
        margin: 0 0 8px 0;
        letter-spacing: -1px;
    }

    .subtitle {
        color: var(--text-muted);
        font-size: 15px;
        margin: 0 0 16px 0;
        line-height: 1.6;
    }

    .tag-list {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .tag {
        padding: 6px 12px;
        background: rgba(255, 179, 71, 0.1);
        border: 1px solid rgba(255, 179, 71, 0.2);
        border-radius: 6px;
        font-size: 13px;
        color: #FF7E5F;
        font-weight: 600;
    }

    .accordion-wrapper {
        margin-top: 24px;
    }

    .accordion-item {
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 12px;
        margin-bottom: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .accordion-item:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .accordion-header {
        padding: 20px 24px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.2s ease;
    }

    .accordion-header:hover {
        background-color: rgba(255, 255, 255, 0.3);
    }

    .store-info-group {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .store-badge {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, rgba(255, 179, 71, 0.15) 0%, rgba(255, 126, 95, 0.15) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FF7E5F;
    }

    .store-meta h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: var(--text-main);
    }

    .store-meta p {
        margin: 4px 0 0 0;
        font-size: 13px;
        color: var(--text-muted);
    }

    .accordion-icon {
        transition: transform 0.3s ease;
        color: var(--text-muted);
        font-size: 14px;
    }

    .accordion-item.active .accordion-icon {
        transform: rotate(180deg);
    }

    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .accordion-item.active .accordion-content {
        max-height: 1000px;
    }

    .history-mini-table {
        padding: 0 24px 24px 24px;
    }

    .history-row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 16px;
        padding: 12px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .history-row:last-child {
        border-bottom: none;
    }

    .history-product {
        font-size: 14px;
        color: var(--text-main);
        font-weight: 500;
    }

    .history-price {
        font-size: 16px;
        font-weight: 700;
        color: #FF7E5F;
    }

    .history-date {
        font-size: 13px;
        color: var(--text-muted);
        text-align: right;
    }

    .link-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        background: linear-gradient(135deg, #FFB347 0%, #FF7E5F 100%);
        color: white;
        font-weight: 700;
        font-size: 13px;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        box-shadow: 0 8px 16px rgba(255, 126, 95, 0.25), 0 0 15px rgba(255, 179, 71, 0.15);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }

    .link-action:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 12px 25px rgba(255, 126, 95, 0.35), 0 0 25px rgba(255, 179, 71, 0.25);
    }

    .empty-state {
        text-align: center;
        padding: 60px 24px;
        color: var(--text-muted);
    }

    .empty-state svg {
        margin: 0 auto 16px;
        opacity: 0.3;
    }
</style>

<div class="product-header-card mb-8">
    <div class="product-image-large">
        <?php if (!empty($product['image']) && file_exists(__DIR__ . '/../../' . $product['image'])): ?>
            <img src="/<?php echo sanitize($product['image']); ?>" alt="<?php echo sanitize($product['name']); ?>">
        <?php else: ?>
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m7.5 4.27 9 5.15"></path>
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                <path d="m3.3 7 8.7 5 8.7-5"></path>
                <path d="M12 22V12"></path>
            </svg>
        <?php endif; ?>
    </div>
    <div class="header-text-group">
        <h1><?php echo sanitize($product['name']); ?></h1>
        <p class="subtitle">
            <?php if (!empty($product['description'])): ?>
                <?php echo nl2br(sanitize((string) $product['description'])); ?>
            <?php else: ?>
                Price trends for this product across your tracked stores
            <?php endif; ?>
        </p>
        
        <?php if (!empty($product['tags'])): ?>
            <div class="tag-list">
                <?php 
                $tags = explode(',', $product['tags']);
                foreach ($tags as $tag): 
                    $tag = trim($tag);
                    if ($tag !== ''):
                ?>
                    <span class="tag"><?php echo sanitize($tag); ?></span>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 24px; display: flex; gap: 12px;">
            <a href="/modules/prices/add.php?product_id=<?php echo (int) $product['id']; ?>" class="link-action">
                + Add Price
            </a>
            <a href="/modules/products/edit.php?id=<?php echo (int) $product['id']; ?>" class="link-action" style="background: rgba(255, 255, 255, 0.8); color: var(--text-main); border: 1px solid rgba(0, 0, 0, 0.05); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);">
                Edit Product
            </a>
        </div>
    </div>
</div>

<?php if (count($stores) === 0): ?>
    <div class="accordion-item">
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 6v6l4 2"></path>
            </svg>
            <p style="font-size: 15px; margin: 0 0 16px 0;">No price data available yet for this product.</p>
            <a href="/modules/prices/add.php?product_id=<?php echo (int) $product['id']; ?>" class="link-action">
                Add First Price
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="accordion-wrapper">
        <?php foreach ($stores as $store): ?>
            <div class="accordion-item">
                <div class="accordion-header">
                    <div class="store-info-group">
                        <div class="store-badge">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"></path>
                                <path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"></path>
                                <path d="M12 3v6"></path>
                            </svg>
                        </div>
                        <div class="store-meta">
                            <h3><?php echo sanitize($store['name']); ?></h3>
                            <p><?php echo sanitize((string) $store['city']); ?><?php if (!empty($store['state'])): ?>, <?php echo sanitize((string) $store['state']); ?><?php endif; ?></p>
                        </div>
                    </div>
                    <div class="accordion-icon">
                        <span>▼</span>
                    </div>
                </div>
                <div class="accordion-content">
                    <div class="history-mini-table">
                        <?php 
                        $storePrices = $pricesByStore[(int)$store['id']] ?? [];
                        if (count($storePrices) > 0):
                            foreach ($storePrices as $priceEntry):
                        ?>
                            <div class="history-row">
                                <span class="history-product">
                                    <?php echo !empty($priceEntry['comments']) ? sanitize($priceEntry['comments']) : 'Price Entry'; ?>
                                </span>
                                <span class="history-price">
                                    ₹<?php echo number_format((float) $priceEntry['selling_price'], 2); ?>
                                    <?php if (!empty($priceEntry['mrp'])): ?>
                                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 500;"> (MRP: ₹<?php echo number_format((float) $priceEntry['mrp'], 2); ?>)</span>
                                    <?php endif; ?>
                                </span>
                                <span class="history-date"><?php echo date('M d, Y', strtotime($priceEntry['created_at'])); ?></span>
                            </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                            <p style="padding: 12px 0; color: var(--text-muted); font-size: 14px;">No price history for this store.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
    // Accordion Toggle Logic
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', () => {
            const item = header.parentElement;
            
            // Toggle active state
            item.classList.toggle('active');
        });
    });
</script>

<?php
render_footer();
