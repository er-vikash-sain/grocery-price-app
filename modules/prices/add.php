<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/footer.php';

$db = get_db();

$productsStmt = $db->query('SELECT id, name FROM products ORDER BY name ASC');
$products = $productsStmt->fetchAll();

$storesStmt = $db->query('SELECT id, name FROM stores ORDER BY name ASC');
$stores = $storesStmt->fetchAll();

$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;
$storeId = isset($_GET['store_id']) ? (int) $_GET['store_id'] : 0;
$price = '';
$unit = '';
$comments = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $storeId = (int) ($_POST['store_id'] ?? 0);
        $priceInput = trim((string) ($_POST['price'] ?? ''));
        $unit = trim((string) ($_POST['unit'] ?? ''));
        $comments = trim((string) ($_POST['comments'] ?? ''));

        if ($productId <= 0 || $storeId <= 0) {
            $error = 'Product and store are required.';
        } elseif ($priceInput === '' || !is_numeric($priceInput) || (float) $priceInput < 0) {
            $error = 'Please enter a valid non-negative price.';
        } elseif ($unit === '') {
            $error = 'Unit is required (kg, gm, litre, etc.).';
        } else {
            $priceValue = (float) $priceInput;

            $stmt = $db->prepare(
                'INSERT INTO prices (product_id, store_id, price, unit, comments, created_at, updated_at)
                 VALUES (:product_id, :store_id, :price, :unit, :comments, NOW(), NOW())'
            );

            $stmt->execute([
                'product_id' => $productId,
                'store_id'   => $storeId,
                'price'      => $priceValue,
                'unit'       => $unit,
                'comments'   => $comments !== '' ? $comments : null,
            ]);

            if ($productId > 0) {
                redirect('/modules/products/view.php?id=' . $productId);
            }

            redirect('/modules/prices/history.php');
        }
    }
}

render_header('Add Price - Price Tracker');

?>
        <?php if ($error !== ''): ?>
            <div class="mb-4 text-red-600 text-sm">
                <?php echo sanitize($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="bg-white shadow-md rounded px-8 pt-6 pb-8">
            <?php echo csrf_field(); ?>
            <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="product_id">Product</label>
                    <select
                        id="product_id"
                        name="product_id"
                        required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    >
                        <option value="">Select product</option>
                        <?php foreach ($products as $product): ?>
                            <option
                                value="<?php echo (int) $product['id']; ?>"
                                <?php echo ((int) $product['id'] === $productId) ? 'selected' : ''; ?>
                            >
                                <?php echo sanitize($product['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="store_id">Store</label>
                    <select
                        id="store_id"
                        name="store_id"
                        required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    >
                        <option value="">Select store</option>
                        <?php foreach ($stores as $store): ?>
                            <option
                                value="<?php echo (int) $store['id']; ?>"
                                <?php echo ((int) $store['id'] === $storeId) ? 'selected' : ''; ?>
                            >
                                <?php echo sanitize($store['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="price">Price</label>
                    <input
                        id="price"
                        name="price"
                        type="number"
                        step="0.01"
                        min="0"
                        value="<?php echo sanitize($price); ?>"
                        required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    >
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="unit">Unit</label>
                    <input
                        id="unit"
                        name="unit"
                        type="text"
                        placeholder="e.g. kg, gm, litre, ml, pcs"
                        value="<?php echo sanitize($unit); ?>"
                        required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    >
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="comments">Comments (optional)</label>
                <textarea
                    id="comments"
                    name="comments"
                    rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                ><?php echo sanitize($comments); ?></textarea>
            </div>

            <div class="flex items-center justify-between">
                <button
                    type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    Save Price
                </button>
            </div>
        </form>
<?php
render_footer();
