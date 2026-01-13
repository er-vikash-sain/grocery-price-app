<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

require_auth();

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

$latestPrices = get_latest_prices_for_product($db, $id);
$cheapest = get_cheapest_store_for_product($db, $id);

// Fetch store names for the comparison table.
$storeNames = [];
if (count($latestPrices) > 0) {
    $storeIds = array_map(static fn(array $row): int => (int) $row['store_id'], $latestPrices);
    $storeIds = array_values(array_unique($storeIds));

    if (count($storeIds) > 0) {
        $placeholders = implode(',', array_fill(0, count($storeIds), '?'));
        $storeStmt = $db->prepare('SELECT id, name FROM stores WHERE id IN (' . $placeholders . ')');
        $storeStmt->execute($storeIds);
        foreach ($storeStmt->fetchAll() as $storeRow) {
            $storeNames[(int) $storeRow['id']] = $storeRow['name'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo sanitize($product['name']); ?> - Product Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex flex-col">
    <header class="bg-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold">Product Details</h1>
            <nav class="space-x-4 text-sm">
                <a href="/modules/products/list.php" class="text-gray-700 hover:underline">Back to Products</a>
                <a href="/index.php" class="text-gray-700 hover:underline">Dashboard</a>
                <a href="/logout.php" class="text-red-600 hover:underline">Logout</a>
            </nav>
        </div>
    </header>
    <main class="flex-1 max-w-6xl mx-auto px-4 py-8 space-y-6">
        <section class="bg-white shadow rounded p-6 flex flex-col md:flex-row gap-6">
            <?php if (!empty($product['image'])): ?>
                <div class="md:w-1/3">
                    <img
                        src="<?php echo sanitize((string) $product['image']); ?>"
                        alt="<?php echo sanitize($product['name']); ?>"
                        class="w-full h-auto rounded object-cover"
                    >
                </div>
            <?php endif; ?>
            <div class="flex-1">
                <h2 class="text-2xl font-semibold mb-2">
                    <?php echo sanitize($product['name']); ?>
                </h2>
                <?php if (!empty($product['description'])): ?>
                    <p class="text-gray-700 mb-4">
                        <?php echo nl2br(sanitize((string) $product['description'])); ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($product['tags'])): ?>
                    <p class="text-sm text-gray-500">
                        Tags:
                        <span class="font-mono">
                            <?php echo sanitize((string) $product['tags']); ?>
                        </span>
                    </p>
                <?php endif; ?>

                <div class="mt-4 space-x-3 text-sm">
                    <a href="/modules/products/edit.php?id=<?php echo (int) $product['id']; ?>" class="text-blue-600 hover:underline">
                        Edit Product
                    </a>
                    <form method="post" action="/modules/products/delete.php" class="inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo (int) $product['id']; ?>">
                        <button
                            type="submit"
                            class="text-red-600 hover:underline"
                            onclick="return confirm('Delete this product? This will also remove its price history.');"
                        >
                            Delete
                        </button>
                    </form>
                </div>

                <?php if ($cheapest !== null): ?>
                    <div class="mt-4 inline-flex items-center px-3 py-2 bg-green-100 text-green-800 text-sm font-semibold rounded">
                        Recommended Store:
                        <span class="ml-2 font-bold">
                            <?php echo sanitize((string) $cheapest['store_name']); ?>
                        </span>
                        <span class="ml-2">
                            (<?php echo number_format((float) $cheapest['price'], 2); ?> per
                            <?php echo sanitize((string) $cheapest['unit']); ?>)
                        </span>
                    </div>
                <?php else: ?>
                    <p class="mt-4 text-sm text-gray-500">No prices recorded yet for this product.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="bg-white shadow rounded p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Price Comparison</h3>
                <a href="/modules/prices/add.php?product_id=<?php echo (int) $product['id']; ?>"
                   class="text-sm text-blue-600 hover:underline">
                    Add Price
                </a>
            </div>
            <?php if (count($latestPrices) === 0): ?>
                <p class="text-gray-600 text-sm">No price data available yet.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
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
                                Last Updated
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($latestPrices as $row): ?>
                            <tr
                                class="<?php echo ($cheapest !== null && (int) $cheapest['id'] === (int) $row['id']) ? 'bg-green-50' : ''; ?>"
                            >
                                <td class="px-4 py-2 text-sm text-gray-900">
                                    <?php
                                    $sid = (int) $row['store_id'];
                                    echo sanitize($storeNames[$sid] ?? (string) $sid);
                                    ?>
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
    </main>
</div>
</body>
</html>
