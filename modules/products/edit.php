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
$currentImage = (string) ($product['image'] ?? '');
$tags = (string) ($product['tags'] ?? '');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $tags = trim((string) ($_POST['tags'] ?? ''));
        $uploadedImagePath = $currentImage; // Keep current image by default

        if ($name === '') {
            $error = 'Product name is required.';
        } else {
            // Handle file upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/products/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (!in_array($fileExtension, $allowedExtensions)) {
                    $error = 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP';
                } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB limit
                    $error = 'File size must be less than 5MB';
                } else {
                    $fileName = uniqid('product_', true) . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                        // Delete old image if it exists
                        if (!empty($currentImage) && file_exists(__DIR__ . '/../../' . $currentImage)) {
                            unlink(__DIR__ . '/../../' . $currentImage);
                        }
                        $uploadedImagePath = 'public/uploads/products/' . $fileName;
                    } else {
                        $error = 'Failed to upload image';
                    }
                }
            }

            if ($error === '') {
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
                    'image'       => $uploadedImagePath !== '' ? $uploadedImagePath : null,
                    'tags'        => $tags !== '' ? $tags : null,
                ]);

                redirect('/modules/products/view.php?id=' . $id);
            }
        }
    }
}

render_header('Edit Product - Price Tracker', 'products');

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

    .premium-file-input {
        cursor: pointer;
    }

    .premium-file-input::file-selector-button {
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 6px;
        padding: 8px 16px;
        margin-right: 12px;
        color: var(--text-main);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .premium-file-input:hover::file-selector-button {
        background: #FFFFFF;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
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

    .file-hint {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 6px;
    }

    .current-image-preview {
        margin-top: 12px;
        padding: 12px;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .current-image-preview img {
        width: 60px;
        height: 60px;
        object-fit: contain;
        border-radius: 6px;
        background: white;
        padding: 4px;
    }

    .current-image-preview span {
        font-size: 13px;
        color: var(--text-muted);
    }
</style>

<header class="mb-8">
    <h1 class="text-3xl font-semibold text-[#0F172A] tracking-tight">Edit Product</h1>
    <p class="subtitle">Update product information and details</p>
</header>

<?php if ($error !== ''): ?>
    <div class="mb-6 p-4 bg-red-50/50 border border-red-100 rounded-xl text-red-600 text-sm font-medium">
        <?php echo sanitize($error); ?>
    </div>
<?php endif; ?>

<div class="content-card form-card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            
            <div class="form-group">
                <label for="name" class="form-label">Product Name</label>
                <input type="text" id="name" name="name" class="premium-input"
                       placeholder="Enter product name" value="<?php echo sanitize($name); ?>" required>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="premium-textarea"
                          placeholder="Enter product description (optional)"><?php echo sanitize($description); ?></textarea>
            </div>

            <div class="form-group">
                <label for="image" class="form-label">Product Image</label>
                <input type="file" id="image" name="image" class="premium-input premium-file-input"
                       accept="image/jpeg,image/png,image/gif,image/webp">
                <p class="file-hint">Upload a new image to replace the current one (Max 5MB)</p>
                
                <?php if (!empty($currentImage) && file_exists(__DIR__ . '/../../' . $currentImage)): ?>
                    <div class="current-image-preview">
                        <img src="/<?php echo sanitize($currentImage); ?>" alt="Current product image">
                        <span>Current image</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="tags" class="form-label">Tags</label>
                <input type="text" id="tags" name="tags" class="premium-input"
                       placeholder="e.g., snacks, beverages, dairy (comma-separated)" value="<?php echo sanitize($tags); ?>">
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" class="link-action">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php
render_footer();
