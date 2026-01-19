<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

function render_header(string $title): void
{
    require_auth();

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo sanitize($title); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="manifest" href="/manifest.json">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="My Personal App">
        <link rel="apple-touch-icon" href="/icons/icon-192.png">

    </head>
    <body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow">
            <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-xl font-semibold">Price Tracker</h1>
                <nav class="space-x-4 text-sm">
                    <a href="/index.php" class="text-gray-700 hover:underline">Dashboard</a>
                    <a href="/modules/stores/list.php" class="text-gray-700 hover:underline">Stores</a>
                    <a href="/modules/products/list.php" class="text-gray-700 hover:underline">Products</a>
                    <a href="/modules/prices/add.php" class="text-gray-700 hover:underline">Add Price</a>
                    <a href="/modules/prices/history.php" class="text-gray-700 hover:underline">History</a>
                    <a href="/logout.php" class="text-red-600 hover:underline">Logout</a>
                </nav>
            </div>
        </header>
        <main class="flex-1 max-w-6xl mx-auto px-4 py-8">
    <?php
}

