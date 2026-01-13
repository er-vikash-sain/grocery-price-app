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

render_header('Products - Price Tracker');

?>
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold">Products</h1>
            <a href="/modules/products/add.php" class="text-sm text-blue-600 hover:underline">Add Product</a>
        </div>
        <form method="get" class="mb-6 flex gap-2 items-center">
            <input
                type="text"
                name="q"
                value="<?php echo sanitize($search); ?>"
                placeholder="Search products by name or tag"
                class="flex-1 shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <button
                type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
            >
                Search
            </button>
        </form>

        <?php if (count($products) === 0): ?>
            <p class="text-gray-600">No products found. <a href="/modules/products/add.php" class="text-blue-600 hover:underline">Add your first product</a>.</p>
        <?php else: ?>
            <div class="overflow-x-auto bg-white shadow rounded">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tags</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                <?php echo sanitize($product['name']); ?>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500">
                                <?php echo sanitize((string) $product['tags']); ?>
                            </td>
                            <td class="px-4 py-2 text-right text-sm space-x-2">
                                <a
                                    href="/modules/products/view.php?id=<?php echo (int) $product['id']; ?>"
                                    class="text-blue-600 hover:underline"
                                >
                                    View
                                </a>
                                <a
                                    href="/modules/products/edit.php?id=<?php echo (int) $product['id']; ?>"
                                    class="text-gray-700 hover:underline"
                                >
                                    Edit
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
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
<?php
render_footer();
