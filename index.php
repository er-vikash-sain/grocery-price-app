<?php

//declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/footer.php';
start_session_if_needed();

if (current_user_id() == null) {
    redirect('/login.php');
}
render_header('Price Tracker Dashboard'); ?>
            <h2 class="text-2xl font-semibold mb-4">Dashboard</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <?php
                $db = get_db();
                $totalProducts = (int) $db->query('SELECT COUNT(*) AS c FROM products WHERE is_active = 1')->fetch()['c'];
                $totalStores = (int) $db->query('SELECT COUNT(*) AS c FROM stores WHERE is_active = 1')->fetch()['c'];
                $totalPrices = (int) $db->query('SELECT COUNT(*) AS c FROM prices')->fetch()['c'];
                ?>
                <div class="bg-white shadow rounded p-4">
                    <div class="text-sm text-gray-500 mb-1">Products</div>
                    <div class="text-2xl font-semibold"><?php echo $totalProducts; ?></div>
                </div>
                <div class="bg-white shadow rounded p-4">
                    <div class="text-sm text-gray-500 mb-1">Stores</div>
                    <div class="text-2xl font-semibold"><?php echo $totalStores; ?></div>
                </div>
                <div class="bg-white shadow rounded p-4">
                    <div class="text-sm text-gray-500 mb-1">Price entries</div>
                    <div class="text-2xl font-semibold"><?php echo $totalPrices; ?></div>
                </div>
            </div>

            <div class="bg-white shadow rounded p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Recent Price Updates</h3>
                    <a href="/modules/prices/history.php" class="text-sm text-blue-600 hover:underline">View all</a>
                </div>
                <?php
                $recentStmt = $db->query(
                    'SELECT p.id, p.price, p.unit, p.created_at, pr.name AS product_name, s.name AS store_name
                     FROM prices p
                     JOIN products pr ON pr.id = p.product_id
                     JOIN stores s ON s.id = p.store_id
                     ORDER BY p.created_at DESC
                     LIMIT 10'
                );
                $recentPrices = $recentStmt->fetchAll();
                ?>
                <?php if (count($recentPrices) === 0): ?>
                    <p class="text-gray-600 text-sm">No price updates yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded At</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recentPrices as $row): ?>
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <?php echo sanitize((string) $row['product_name']); ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <?php echo sanitize((string) $row['store_name']); ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <?php echo number_format((float) $row['price'], 2); ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        <?php echo sanitize((string) $row['unit']); ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-500">
                                        <?php echo sanitize((string) $row['created_at']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
<?php
render_footer();
