<?php

//declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';
start_session_if_needed();

if (current_user_id() == null) {
    redirect('/login.php');
}

$db = get_db();
$totalProducts = (int) $db->query('SELECT COUNT(*) AS c FROM products WHERE is_active = 1')->fetch()['c'];
$totalStores = (int) $db->query('SELECT COUNT(*) AS c FROM stores WHERE is_active = 1')->fetch()['c'];
$totalPrices = (int) $db->query('SELECT COUNT(*) AS c FROM prices')->fetch()['c'];

$recentStmt = $db->query(
    'SELECT p.id, p.price, p.unit, p.created_at, pr.name AS product_name, s.name AS store_name
     FROM prices p
     JOIN products pr ON pr.id = p.product_id
     JOIN stores s ON s.id = p.store_id
     ORDER BY p.created_at DESC
     LIMIT 10'
);
$recentPrices = $recentStmt->fetchAll();

render_header('Price Tracker Dashboard', 'dashboard');
?>

<style>
    .stat-card {
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 12px;
        padding: 32px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.4s ease;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        overflow: hidden;
        position: relative;
    }

    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 30px 60px rgba(255, 126, 95, 0.12), 0 0 0 1px rgba(255, 255, 255, 0.8);
    }

    .stat-icon-wrapper {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.15;
        transition: all 0.5s ease;
        z-index: 1;
    }

    .stat-card:hover .stat-icon-wrapper {
        transform: scale(1.2) rotate(-10deg);
        opacity: 0.25;
    }

    .stat-card.products .stat-icon-wrapper { color: #FF7E5F; }
    .stat-card.stores .stat-icon-wrapper { color: #FFB347; }
    .stat-card.entries .stat-icon-wrapper { color: #60A5FA; }

    .stat-label {
        color: var(--text-muted);
        font-size: 18px;
        font-weight: 500;
        letter-spacing: -0.2px;
        margin-bottom: 4px;
        z-index: 2;
    }

    .stat-value {
        font-size: 40px;
        font-weight: 800;
        line-height: 1;
        z-index: 2;
    }

    .stat-card.products .stat-value { color: #FF7E5F; }
    .stat-card.stores .stat-value { color: #FFB347; }
    .stat-card.entries .stat-value { color: #60A5FA; }

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
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: rgba(255, 255, 255, 0.2);
    }

    .card-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
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
    }

    .link-action:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 12px 25px rgba(255, 126, 95, 0.35), 0 0 25px rgba(255, 179, 71, 0.25);
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
</style>

<header class="mb-8">
    <h1 class="text-3xl font-extrabold text-[#0F172A] tracking-tight">Dashboard</h1>
</header>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
    <div class="stat-card products">
        <span class="stat-icon-wrapper">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m7.5 4.27 9 5.15"></path>
                <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                <path d="m3.3 7 8.7 5 8.7-5"></path>
                <path d="M12 22V12"></path>
            </svg>
        </span>
        <div class="stat-label">Products</div>
        <div class="stat-value"><?php echo $totalProducts; ?></div>
    </div>
    <div class="stat-card stores">
        <span class="stat-icon-wrapper">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"></path>
                <path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"></path>
                <path d="M12 3v6"></path>
            </svg>
        </span>
        <div class="stat-label">Stores</div>
        <div class="stat-value"><?php echo $totalStores; ?></div>
    </div>
    <div class="stat-card entries">
        <span class="stat-icon-wrapper">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
                <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
                <path d="M18 12a2 2 0 0 0 0 4h4v-4Z"></path>
            </svg>
        </span>
        <div class="stat-label">Price entries</div>
        <div class="stat-value"><?php echo $totalPrices; ?></div>
    </div>
</div>

<!-- Recent Price Updates -->
<div class="content-card">
    <div class="card-header">
        <div>
            <h2>Recent Price Updates</h2>
            <p class="card-subtitle">Latest tracked updates from your stores</p>
        </div>
        <a href="/modules/prices/history.php" class="link-action">View All</a>
    </div>
    <div class="overflow-x-auto">
        <?php if (count($recentPrices) === 0): ?>
            <p class="p-6 text-[#475569] text-sm">No price updates yet.</p>
        <?php else: ?>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Store</th>
                        <th>Price</th>
                        <th>Unit</th>
                        <th>Recorded At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentPrices as $row): ?>
                        <tr>
                            <td class="font-bold"><?php echo sanitize((string) $row['product_name']); ?></td>
                            <td><?php echo sanitize((string) $row['store_name']); ?></td>
                            <td class="font-semibold text-[#FF7E5F]">₹<?php echo number_format((float) $row['price'], 2); ?></td>
                            <td class="text-[#475569]"><?php echo sanitize((string) $row['unit']); ?></td>
                            <td class="text-[#475569] text-sm"><?php echo sanitize((string) $row['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
render_footer();
