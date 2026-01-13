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

render_header(sanitize($store['name']) . ' - Store Details');

?>
    <div class="space-y-6">
        <section class="bg-white shadow rounded p-6">
            <h2 class="text-2xl font-semibold mb-2">
                <?php echo sanitize($store['name']); ?>
            </h2>
            <?php if (!empty($store['address'])): ?>
                <p class="text-gray-700 mb-2">
                    <?php echo nl2br(sanitize((string) $store['address'])); ?>
                </p>
            <?php endif; ?>
            <p class="text-gray-600 text-sm">
                <?php if (!empty($store['city'])): ?>
                    <?php echo sanitize((string) $store['city']); ?>
                <?php endif; ?>
                <?php if (!empty($store['state'])): ?>
                    <?php if (!empty($store['city'])): ?>, <?php endif; ?>
                    <?php echo sanitize((string) $store['state']); ?>
                <?php endif; ?>
            </p>
        </section>

        <section class="bg-white shadow rounded p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Current Prices in this Store</h3>
                <a href="/modules/prices/add.php?store_id=<?php echo $id; ?>" class="text-sm text-blue-600 hover:underline">
                    Add Price
                </a>
            </div>
            <?php if (count($latestPrices) === 0): ?>
                <p class="text-gray-600 text-sm">No prices recorded yet for this store.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unit
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Last Updated
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($latestPrices as $row): ?>
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <a href="/modules/products/view.php?id=<?php echo (int) $row['product_id']; ?>"
                                       class="text-blue-600 hover:underline">
                                        <?php echo sanitize((string) $row['product_name']); ?>
                                    </a>
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
        </section>

        <section class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Price History</h3>
            <?php if (count($history) === 0): ?>
                <p class="text-gray-600 text-sm">No historical price data yet.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unit
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Comments
                            </th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Recorded At
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <a href="/modules/products/view.php?id=<?php echo (int) $row['product_id']; ?>"
                                       class="text-blue-600 hover:underline">
                                        <?php echo sanitize((string) $row['product_name']); ?>
                                    </a>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <?php echo number_format((float) $row['price'], 2); ?>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <?php echo sanitize((string) $row['unit']); ?>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-500">
                                    <?php echo sanitize((string) $row['comments']); ?>
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
        </section>
    </div>
<?php
render_footer();
