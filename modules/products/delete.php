<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/modules/products/list.php');
}

if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    redirect('/modules/products/list.php');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id > 0) {
    $db = get_db();
    $stmt = $db->prepare('UPDATE products SET is_active = 0, updated_at = NOW() WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

redirect('/modules/products/list.php');
