<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/footer.php';

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

$name = (string) $product['name'];
$description = (string) ($product['description'] ?? '');
$image = (string) ($product['image'] ?? '');
$tags = (string) ($product['tags'] ?? '');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $image = trim((string) ($_POST['image'] ?? ''));
        $tags = trim((string) ($_POST['tags'] ?? ''));

        if ($name === '') {
            $error = 'Product name is required.';
        } else {
            $update = $db->prepare(
                'UPDATE products
                 SET name = :name,
                     description = :description,
                     image = :image,
                     tags = :tags,
                     updated_at = NOW()
                 WHERE id = :id'
            );

            $update->execute([
                'id'          => $id,
                'name'        => $name,
                'description' => $description !== '' ? $description : null,
                'image'       => $image !== '' ? $image : null,
                'tags'        => $tags !== '' ? $tags : null,
            ]);

            redirect('/modules/products/view.php?id=' . $id);
        }
    }
}

render_header('Edit Product - Price Tracker');

?>
        <?php if ($error !== ''): ?>
            <div class="mb-4 text-red-600 text-sm">
                <?php echo sanitize($error); ?>
            </div>
        <?php endif; ?>
        <form method="post" class="bg-white shadow-md rounded px-8 pt-6 pb-8">
            <?php echo csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="<?php echo sanitize($name); ?>"
                    required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                >
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                ><?php echo sanitize($description); ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="image">Image Path (optional)</label>
                <input
                    id="image"
                    name="image"
                    type="text"
                    value="<?php echo sanitize($image); ?>"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                >
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="tags">Tags (comma-separated, optional)</label>
                <input
                    id="tags"
                    name="tags"
                    type="text"
                    value="<?php echo sanitize($tags); ?>"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                >
            </div>
            <div class="flex items-center justify-between">
                <button
                    type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    Save Changes
                </button>
            </div>
        </form>
<?php
render_footer();
