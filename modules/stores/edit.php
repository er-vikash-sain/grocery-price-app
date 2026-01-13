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

$name = (string) $store['name'];
$address = (string) ($store['address'] ?? '');
$city = (string) ($store['city'] ?? '');
$state = (string) ($store['state'] ?? '');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $state = trim((string) ($_POST['state'] ?? ''));

        if ($name === '') {
            $error = 'Store name is required.';
        } else {
            $update = $db->prepare(
                'UPDATE stores
                 SET name = :name,
                     address = :address,
                     city = :city,
                     state = :state,
                     updated_at = NOW()
                 WHERE id = :id'
            );

            $update->execute([
                'id'      => $id,
                'name'    => $name,
                'address' => $address !== '' ? $address : null,
                'city'    => $city !== '' ? $city : null,
                'state'   => $state !== '' ? $state : null,
            ]);

            redirect('/modules/stores/list.php');
        }
    }
}

render_header('Edit Store - Price Tracker');

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
                <label class="block text-gray-700 text-sm font-bold mb-2" for="address">Address</label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                ><?php echo sanitize($address); ?></textarea>
            </div>
            <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="city">City</label>
                    <input
                        id="city"
                        name="city"
                        type="text"
                        value="<?php echo sanitize($city); ?>"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    >
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="state">State</label>
                    <input
                        id="state"
                        name="state"
                        type="text"
                        value="<?php echo sanitize($state); ?>"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    >
                </div>
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
    </main>
</div>
</body>
</html>
