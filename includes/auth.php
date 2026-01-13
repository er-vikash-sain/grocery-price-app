<?php

declare(strict_types=1);

function start_session_if_needed(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function login_user(array $user): void
{
    start_session_if_needed();

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_email'] = $user['email'];
}

function logout_user(): void
{
    start_session_if_needed();

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function current_user_id(): ?int
{
    start_session_if_needed();

    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function require_auth(): void
{
    if (current_user_id() === null) {
        $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        redirect('/login.php?redirect=' . $redirect);
    }
}
