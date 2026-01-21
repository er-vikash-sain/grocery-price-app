<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/footer.php';

$db = get_db();

$stmt = $db->query('SELECT * FROM stores WHERE is_active = 1 ORDER BY name ASC');
$stores = $stmt->fetchAll();

render_header('Stores - Price Tracker', 'stores');

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

    .product-name {
        font-weight: 700;
        color: var(--text-main);
    }

    .product-unit {
        color: var(--text-muted);
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
</style>

<header class="mb-8">
    <div class="flex justify-between items-center mb-2">
        <h1 class="text-3xl font-semibold text-[#0F172A] tracking-tight">Stores</h1>
        <a href="/modules/stores/add.php" class="link-action">
            <span>+ Add Store</span>
        </a>
    </div>
    <p class="subtitle">Manage and track your primary grocery locations</p>
</header>

<?php if (count($stores) === 0): ?>
    <div class="content-card">
        <div class="p-8 text-center">
            <p class="text-[#475569] mb-4">No stores found.</p>
            <a href="/modules/stores/add.php" class="link-action">Add your first store</a>
        </div>
    </div>
<?php else: ?>
    <div class="content-card">
        <div class="card-header">
            <div>
                <h2>Registered Stores</h2>
                <p class="card-subtitle">Detailed list of all tracked stores</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>City</th>
                        <th>State</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; ?>
                    <?php foreach ($stores as $store): ?>
                        <tr>
                            <td class="col-num"><?php echo $counter++; ?></td>
                            <td class="product-name"><?php echo sanitize($store['name']); ?></td>
                            <td class="product-unit"><?php echo sanitize((string) $store['city']); ?></td>
                            <td class="product-unit"><?php echo sanitize((string) $store['state']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="/modules/stores/edit.php?id=<?php echo (int) $store['id']; ?>" 
                                       class="link-action secondary mini">Edit</a>
                                    <form method="post" action="/modules/stores/delete.php" class="inline" style="margin: 0;">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int) $store['id']; ?>">
                                        <button type="submit" class="link-action danger mini"
                                                onclick="return confirm('Deactivate this store? Existing price records will remain for history.');">
                                            Deactivate
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
