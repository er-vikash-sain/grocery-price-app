<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/footer.php';

$db = get_db();

$search = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $search = trim((string) ($_GET['q'] ?? ''));
}

if ($search !== '') {
    $stmt = $db->prepare(
        'SELECT * FROM products WHERE is_active = 1 AND (name LIKE :term OR tags LIKE :term) ORDER BY name ASC'
    );
    $stmt->execute(['term' => '%' . $search . '%']);
} else {
    $stmt = $db->query('SELECT * FROM products WHERE is_active = 1 ORDER BY name ASC');
}

$products = $stmt->fetchAll();

render_header('Products - Price Tracker', 'products');

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

    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.4);
        background-color: rgba(255, 255, 255, 0.2);
    }

    .card-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 500;
        color: var(--text-main);
        letter-spacing: -0.8px;
    }

    .card-subtitle {
        margin: 4px 0 0 0;
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 500;
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

    .link-action.mini {
        padding: 8px 16px;
        font-size: 12px;
    }

    .link-action.secondary {
        background: rgba(255, 255, 255, 0.8);
        color: var(--text-main);
        border: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .link-action.secondary:hover {
        background: #FFFFFF;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .link-action.danger {
        background: linear-gradient(135deg, #FF5F6D 0%, #FFC371 100%);
        box-shadow: 0 8px 16px rgba(255, 95, 109, 0.25);
    }

    .link-action.danger:hover {
        box-shadow: 0 12px 25px rgba(255, 95, 109, 0.35);
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .modern-table thead {
        background-color: rgba(0, 0, 0, 0.02);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .modern-table th {
        padding: 16px 24px;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modern-table td {
        padding: 16px 24px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        color: var(--text-main);
        font-size: 15px;
    }

    .modern-table tr:last-child td {
        border-bottom: none;
    }

    .modern-table tr:hover td {
        background-color: rgba(255, 126, 95, 0.02);
    }

    .col-num {
        font-weight: 700;
        color: var(--text-muted);
        width: 40px;
    }

    .table-img-container {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        overflow: hidden;
        background-color: #f8fafc;
        border: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .table-img-container img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .modern-table tr:hover .table-img-container img {
        transform: scale(1.1);
    }

    .product-name {
        font-weight: 700;
        color: var(--text-main);
    }

    .product-tags {
        color: var(--text-muted);
        font-size: 13px;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .subtitle {
        color: var(--text-muted);
        font-size: 15px;
        margin: 8px 0 0 0;
    }

    .search-box {
        position: relative;
        flex: 1;
        max-width: 400px;
    }

    .search-box .premium-input {
        padding-left: 44px;
        width: 100%;
        padding: 14px 16px 14px 44px;
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

    .search-box .premium-input:focus {
        background-color: #FFFFFF;
        border-color: #FFB347;
        box-shadow: 0 0 0 4px rgba(255, 179, 71, 0.15);
    }

    .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }
</style>

<header class="mb-8">
    <div class="flex justify-between items-center mb-2">
        <h1 class="text-3xl font-semibold text-[#0F172A] tracking-tight">Products</h1>
        <a href="/modules/products/add.php" class="link-action">
            <span>+ Add Product</span>
        </a>
    </div>
    <p class="subtitle">Manage and track your product catalog</p>
</header>

<form method="GET" class="mb-6 flex gap-3 items-center">
    <div class="search-box">
        <span class="search-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
        </span>
        <input type="text" name="q" class="premium-input" 
               placeholder="Search products by name or tag..." 
               value="<?php echo sanitize($search); ?>">
    </div>
    <button type="submit" class="link-action">Search</button>
</form>

<?php if (count($products) === 0): ?>
    <div class="content-card">
        <div class="p-8 text-center">
            <p class="text-[#475569] mb-4">No products found.</p>
            <a href="/modules/products/add.php" class="link-action">Add your first product</a>
        </div>
    </div>
<?php else: ?>
    <div class="content-card">
        <div class="card-header">
            <div>
                <h2>Product Catalog</h2>
                <p class="card-subtitle">All tracked products with details</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Tags</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="col-num"><?php echo $counter++; ?></td>
                            <td>
                                <div class="table-img-container">
                                    <?php if (!empty($product['image']) && file_exists(__DIR__ . '/../../' . $product['image'])): ?>
                                        <img src="/<?php echo sanitize($product['image']); ?>" alt="<?php echo sanitize($product['name']); ?>">
                                    <?php else: ?>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m7.5 4.27 9 5.15"></path>
                                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                                            <path d="m3.3 7 8.7 5 8.7-5"></path>
                                            <path d="M12 22V12"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="product-name"><?php echo sanitize($product['name']); ?></td>
                            <td class="product-tags"><?php echo sanitize((string) $product['tags']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="/modules/products/edit.php?id=<?php echo (int) $product['id']; ?>" 
                                       class="link-action secondary mini">Edit</a>
                                    <form method="post" action="/modules/products/delete.php" class="inline" style="margin: 0;">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int) $product['id']; ?>">
                                        <button type="submit" class="link-action danger mini"
                                                onclick="return confirm('Delete this product? This will also remove its price history.');">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php
render_footer();
