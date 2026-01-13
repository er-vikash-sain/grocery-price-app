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

render_header('Price History - Price Tracker');

?>
        <?php if ($productId > 0 && $product !== null): ?>
            <h2 class="text-lg font-semibold mb-4">
                History for:
                <span class="font-bold"><?php echo sanitize($product['name']); ?></span>
            </h2>
        <?php else: ?>
            <p class="text-sm text-gray-600 mb-4">
                Showing latest price entries (up to 100 rows).
                Use product detail pages to see comparison view.
            </p>
        <?php endif; ?>

        <?php if (count($rows) === 0): ?>
            <p class="text-gray-600">No price history found.</p>
        <?php else: ?>
            <div class="overflow-x-auto bg-white shadow rounded">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <?php if ($productId === 0): ?>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                        <?php endif; ?>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Store
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
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php if ($productId === 0): ?>
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <?php echo sanitize((string) $row['product_name']); ?>
                                </td>
                            <?php endif; ?>
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
    </main>
</div>
</body>
</html>
