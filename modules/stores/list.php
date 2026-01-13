<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/footer.php';

$db = get_db();

$stmt = $db->query('SELECT * FROM stores WHERE is_active = 1 ORDER BY name ASC');
$stores = $stmt->fetchAll();

render_header('Stores - Price Tracker');

?>
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold">Stores</h1>
            <a href="/modules/stores/add.php" class="text-sm text-blue-600 hover:underline">Add Store</a>
        </div>
        <?php if (count($stores) === 0): ?>
            <p class="text-gray-600">No stores found. <a href="/modules/stores/add.php" class="text-blue-600 hover:underline">Add your first store</a>.</p>
        <?php else: ?>
            <div class="overflow-x-auto bg-white shadow rounded">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">State</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($stores as $store): ?>
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">
                                <a href="/modules/stores/view.php?id=<?php echo (int) $store['id']; ?>"
                                   class="text-blue-600 hover:underline">
                                    <?php echo sanitize($store['name']); ?>
                                </a>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500">
                                <?php echo sanitize((string) $store['city']); ?>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-500">
                                <?php echo sanitize((string) $store['state']); ?>
                            </td>
                            <td class="px-4 py-2 text-right text-sm space-x-2">
                                <a
                                    href="/modules/stores/edit.php?id=<?php echo (int) $store['id']; ?>"
                                    class="text-gray-700 hover:underline"
                                >
                                    Edit
                                </a>
                                <form method="post" action="/modules/stores/delete.php" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int) $store['id']; ?>">
                                    <button
                                        type="submit"
                                        class="text-red-600 hover:underline"
                                        onclick="return confirm('Deactivate this store? Existing price records will remain for history.');"
                                    >
                                        Deactivate
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
