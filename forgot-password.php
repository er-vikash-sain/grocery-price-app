<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

start_session_if_needed();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $message = 'Invalid request. Please try again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));

        if ($email !== '') {
            $db = get_db();
            $stmt = $db->prepare('SELECT id, email FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user) {
                $raw = random_bytes(32);
                $token = bin2hex($raw);
                $expiresAt = date('Y-m-d H:i:s', time() + 3600);

                $insert = $db->prepare(
                    'INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)'
                );
                $insert->execute([
                    'user_id'    => $user['id'],
                    'token'      => $token,
                    'expires_at' => $expiresAt,
                ]);

                $baseUrl = rtrim((string) ($_SERVER['HTTP_ORIGIN'] ?? ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'))), '/');
                $resetLink = $baseUrl . '/reset-password.php?token=' . urlencode($token);

                $message = 'If this email exists in our system, you will receive a reset link. For development: ' . $resetLink;
            } else {
                $message = 'If this email exists in our system, you will receive a reset link.';
            }
        } else {
            $message = 'If this email exists in our system, you will receive a reset link.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Price Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 w-full max-w-md">
            <h1 class="text-2xl font-semibold mb-6 text-center">Forgot Password</h1>
            <?php if ($message !== ''): ?>
                <div class="mb-4 text-blue-600 text-sm">
                    <?php echo sanitize($message); ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <?php echo csrf_field(); ?>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                    <input id="email" name="email" type="email" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Send Reset Link
                    </button>
                    <a href="/login.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
