<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/footer.php';

$db = get_db();

$productsStmt = $db->query('SELECT id, name FROM products WHERE is_active = 1 ORDER BY name ASC');
$products = $productsStmt->fetchAll();

$storesStmt = $db->query('SELECT id, name FROM stores WHERE is_active = 1 ORDER BY name ASC');
$stores = $storesStmt->fetchAll();

$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
$storeId = isset($_GET['store_id']) ? (int) $_GET['store_id'] : 0;
$sellingPrice = '';
$mrp = '';
$comments = '';
$error = '';

// Get product name if product_id is provided
$productName = '';
if ($productId > 0) {
    $productStmt = $db->prepare('SELECT name FROM products WHERE id = :id');
    $productStmt->execute(['id' => $productId]);
    $productData = $productStmt->fetch();
    if ($productData) {
        $productName = $productData['name'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $storeId = (int) ($_POST['store_id'] ?? 0);
        $sellingPriceInput = trim((string) ($_POST['selling_price'] ?? ''));
        $mrpInput = trim((string) ($_POST['mrp'] ?? ''));
        $comments = trim((string) ($_POST['comments'] ?? ''));

        if ($productId <= 0 || $storeId <= 0) {
            $error = 'Product and store are required.';
        } elseif ($sellingPriceInput === '' || !is_numeric($sellingPriceInput) || (float) $sellingPriceInput < 0) {
            $error = 'Please enter a valid selling price.';
        } elseif ($mrpInput !== '' && (!is_numeric($mrpInput) || (float) $mrpInput < 0)) {
            $error = 'Please enter a valid MRP.';
        } else {
            $sellingPriceValue = (float) $sellingPriceInput;
            $mrpValue = $mrpInput !== '' ? (float) $mrpInput : null;

            $stmt = $db->prepare(
                'INSERT INTO prices (product_id, store_id, selling_price, mrp, comments, created_at, updated_at)
                 VALUES (:product_id, :store_id, :selling_price, :mrp, :comments, NOW(), NOW())'
            );

            $stmt->execute([
                'product_id'    => $productId,
                'store_id'      => $storeId,
                'selling_price' => $sellingPriceValue,
                'mrp'           => $mrpValue,
                'comments'      => $comments !== '' ? $comments : null,
            ]);

            if ($productId > 0) {
                redirect('/modules/products/view.php?id=' . $productId);
            }

            redirect('/modules/prices/history.php');
        }
    }
}

render_header('Add Price - Price Tracker', 'add-price');

?>

<style>
    .content-card {
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .form-card {
        max-width: 700px;
        margin: 0 auto;
    }

    .card-body {
        padding: 40px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 10px;
        letter-spacing: -0.2px;
    }

    .premium-input,
    .premium-select,
    .premium-textarea {
        width: 100%;
        padding: 14px 16px;
        background-color: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        font-family: inherit;
        font-size: 15px;
        color: var(--text-main);
        transition: all 0.3s ease;
        outline: none;
    }

    .premium-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .premium-input:focus,
    .premium-select:focus,
    .premium-textarea:focus {
        background-color: #FFFFFF;
        border-color: #FFB347;
        box-shadow: 0 0 0 4px rgba(255, 179, 71, 0.15);
    }

    .premium-input::placeholder,
    .premium-textarea::placeholder {
        color: #94A3B8;
    }

    .link-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: linear-gradient(135deg, #FFB347 0%, #FF7E5F 100%);
        color: white;
        font-weight: 700;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        box-shadow: 0 10px 20px rgba(255, 126, 95, 0.3), 0 0 20px rgba(255, 179, 71, 0.2);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
        width: 100%;
    }

    .link-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 25px rgba(255, 126, 95, 0.4), 0 0 30px rgba(255, 179, 71, 0.3);
    }

    .link-action:active {
        transform: translateY(0) scale(0.98);
    }

    .subtitle {
        color: var(--text-muted);
        font-size: 15px;
        margin: 8px 0 0 0;
    }

    .field-hint {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 6px;
    }
</style>

<header class="mb-8">
    <h1 class="text-3xl font-semibold text-[#0F172A] tracking-tight">Add Price Entry</h1>
    <p class="subtitle">
        <?php if ($productName !== ''): ?>
            Record a new price for <strong><?php echo sanitize($productName); ?></strong>
        <?php else: ?>
            Record a new price entry for a product
        <?php endif; ?>
    </p>
</header>

<?php if ($error !== ''): ?>
    <div class="mb-6 p-4 bg-red-50/50 border border-red-100 rounded-xl text-red-600 text-sm font-medium">
        <?php echo sanitize($error); ?>
    </div>
<?php endif; ?>

<div class="content-card form-card">
    <div class="card-body">
        <form method="POST">
            <?php echo csrf_field(); ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="product_id" class="form-label">Product</label>
                    <select id="product_id" name="product_id" class="premium-select" required>
                        <option value="">Select product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo (int) $product['id']; ?>"
                                    <?php echo ((int) $product['id'] === $productId) ? 'selected' : ''; ?>>
                                <?php echo sanitize($product['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="store_id" class="form-label">Store</label>
                    <select id="store_id" name="store_id" class="premium-select" required>
                        <option value="">Select store</option>
                        <?php foreach ($stores as $store): ?>
                            <option value="<?php echo (int) $store['id']; ?>"
                                    <?php echo ((int) $store['id'] === $storeId) ? 'selected' : ''; ?>>
                                <?php echo sanitize($store['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="selling_price" class="form-label">Selling Price (₹)</label>
                    <input type="number" id="selling_price" name="selling_price" class="premium-input"
                           placeholder="0.00" step="0.01" min="0" value="<?php echo sanitize($sellingPrice); ?>" required>
                    <p class="field-hint">Actual price you paid at the store</p>
                </div>
                <div class="form-group">
                    <label for="mrp" class="form-label">MRP (₹)</label>
                    <input type="number" id="mrp" name="mrp" class="premium-input"
                           placeholder="0.00" step="0.01" min="0" value="<?php echo sanitize($mrp); ?>">
                    <p class="field-hint">Maximum Retail Price (optional)</p>
                </div>
            </div>

            <div class="form-group">
                <label for="comments" class="form-label">Comments</label>
                <textarea id="comments" name="comments" class="premium-textarea"
                          placeholder="Add any notes about this price entry (optional)"><?php echo sanitize($comments); ?></textarea>
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" class="link-action">
                    Save Price Entry
                </button>
            </div>
        </form>
    </div>
</div>

<?php
render_footer();
