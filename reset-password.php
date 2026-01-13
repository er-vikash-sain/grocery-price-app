<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

start_session_if_needed();

$db = get_db();
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$token = is_string($token) ? trim($token) : '';

$error = '';
$success = '';
$validToken = null;

if ($token !== '') {
    $stmt = $db->prepare(
        'SELECT pr.*, u.email 
         FROM password_resets pr 
         JOIN users u ON u.id = pr.user_id 
         WHERE pr.token = :token 
         LIMIT 1'
    );
    $stmt->execute(['token' => $token]);
    $validToken = $stmt->fetch();

    if (!$validToken) {
        $error = 'Invalid or expired link.';
    } else {
        $now = new DateTimeImmutable('now');
        $expires = new DateTimeImmutable($validToken['expires_at']);

        if ($now > $expires || $validToken['used_at'] !== null) {
            $error = 'This link is no longer valid.';
            $validToken = null;
        }
    }
} else {
    $error = 'Invalid or expired link.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken !== null) {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($password === '' || $passwordConfirm === '') {
            $error = 'Password and confirmation are required.';
        } elseif ($password !== $passwordConfirm) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $db->beginTransaction();
            try {
                $updateUser = $db->prepare('UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :user_id');
                $updateUser->execute([
                    'hash'    => $hash,
                    'user_id' => $validToken['user_id'],
                ]);

                $updateToken = $db->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
                $updateToken->execute(['id' => $validToken['id']]);

                $db->commit();

                $success = 'Password has been reset successfully. You can now log in.';
            } catch (Throwable $e) {
                $db->rollBack();
                $error = 'Could not reset password. Please try again.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Price Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 w-full max-w-md">
            <h1 class="text-2xl font-semibold mb-6 text-center">Reset Password</h1>
            <?php if ($error !== ''): ?>
                <div class="mb-4 text-red-600 text-sm">
                    <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="mb-4 text-green-600 text-sm">
                    <?php echo sanitize($success); ?>
                </div>
                <div class="text-center mt-4">
                    <a href="/login.php" class="text-blue-500 hover:text-blue-800 font-semibold">Go to Login</a>
                </div>
            <?php elseif ($validToken !== null): ?>
                <form method="post">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="token" value="<?php echo sanitize($token); ?>">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">New Password</label>
                        <input id="password" name="password" type="password" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password_confirm">Confirm Password</label>
                        <input id="password_confirm" name="password_confirm" type="password" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Reset Password
                        </button>
                        <a href="/login.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                            Back to Login
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center">
                    <a href="/forgot-password.php" class="text-blue-500 hover:text-blue-800 font-semibold">Request a new reset link</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
