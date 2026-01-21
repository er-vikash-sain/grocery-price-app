<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/footer.php';

$db = get_db();

$name = '';
$address = '';
$city = '';
$state = '';
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
            $stmt = $db->prepare(
                'INSERT INTO stores (name, address, city, state, is_active, created_at, updated_at)
                 VALUES (:name, :address, :city, :state, 1, NOW(), NOW())'
            );

            $stmt->execute([
                'name'    => $name,
                'address' => $address !== '' ? $address : null,
                'city'    => $city !== '' ? $city : null,
                'state'   => $state !== '' ? $state : null,
            ]);

            redirect('/modules/stores/list.php');
        }
    }
}

render_header('Add Store - Price Tracker', 'stores');

?>

<style>
    .content-card {
        background-color: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .form-card {
        max-width: 700px;
        margin: 0 auto;
    }

    .card-body {
        padding: 40px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 10px;
        letter-spacing: -0.2px;
    }

    .premium-input,
    .premium-textarea {
        width: 100%;
        padding: 14px 16px;
        background-color: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        font-family: inherit;
        font-size: 15px;
        color: var(--text-main);
        transition: all 0.3s ease;
        outline: none;
    }

    .premium-textarea {
        min-height: 120px;
        resize: vertical;
    }

    .premium-input:focus,
    .premium-textarea:focus {
        background-color: #FFFFFF;
        border-color: #FFB347;
        box-shadow: 0 0 0 4px rgba(255, 179, 71, 0.15);
    }

    .premium-input::placeholder,
    .premium-textarea::placeholder {
        color: #94A3B8;
    }

    .link-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: linear-gradient(135deg, #FFB347 0%, #FF7E5F 100%);
        color: white;
        font-weight: 700;
        font-size: 16px;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        box-shadow: 0 10px 20px rgba(255, 126, 95, 0.3), 0 0 20px rgba(255, 179, 71, 0.2);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
        width: 100%;
    }

    .link-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 25px rgba(255, 126, 95, 0.4), 0 0 30px rgba(255, 179, 71, 0.3);
    }

    .link-action:active {
        transform: translateY(0) scale(0.98);
    }

    .subtitle {
        color: var(--text-muted);
        font-size: 15px;
        margin: 8px 0 0 0;
    }
</style>

<header class="mb-8">
    <h1 class="text-3xl font-semibold text-[#0F172A] tracking-tight">Add Store</h1>
    <p class="subtitle">Create a new tracking location for price monitoring</p>
</header>

<?php if ($error !== ''): ?>
    <div class="mb-6 p-4 bg-red-50/50 border border-red-100 rounded-xl text-red-600 text-sm font-medium">
        <?php echo sanitize($error); ?>
    </div>
<?php endif; ?>

<div class="content-card form-card">
    <div class="card-body">
        <form method="POST">
            <?php echo csrf_field(); ?>
            
            <div class="form-group">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" class="premium-input"
                       placeholder="Enter store name" value="<?php echo sanitize($name); ?>" required>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <textarea id="address" name="address" class="premium-textarea"
                          placeholder="Enter store address"><?php echo sanitize($address); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city" class="form-label">City</label>
                    <input type="text" id="city" name="city" class="premium-input"
                           placeholder="Enter city" value="<?php echo sanitize($city); ?>">
                </div>
                <div class="form-group">
                    <label for="state" class="form-label">State</label>
                    <input type="text" id="state" name="state" class="premium-input"
                           placeholder="Enter state" value="<?php echo sanitize($state); ?>">
                </div>
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" class="link-action">
                    Save Store
                </button>
            </div>
        </form>
    </div>
</div>

<?php
render_footer();
