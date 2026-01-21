<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

function render_header(string $title, string $activePage = 'dashboard'): void
{
    require_auth();

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
        <title><?php echo sanitize($title); ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- PWA Meta Tags -->
        <link rel="manifest" href="/manifest.json">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="theme-color" content="#FFF9F2">
        <link rel="apple-touch-icon" href="/icons/icon-192.png">

        <style>
            :root {
                --text-main: #0F172A;
                --text-muted: #475569;
            }

            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #FFF9F2;
                background-image:
                    radial-gradient(circle at 10% 20%, rgba(255, 179, 71, 0.15) 0%, transparent 40%),
                    radial-gradient(circle at 90% 80%, rgba(255, 126, 95, 0.1) 0%, transparent 40%),
                    radial-gradient(circle at 50% 50%, rgba(255, 230, 150, 0.2) 0%, transparent 60%);
            }

            .blob {
                filter: blur(80px);
                opacity: 0.2;
                animation: float 20s infinite alternate cubic-bezier(0.45, 0, 0.55, 1);
            }

            @keyframes float {
                0% { transform: translate(0, 0) scale(1) rotate(0deg); }
                33% { transform: translate(30px, -50px) scale(1.1) rotate(5deg); }
                66% { transform: translate(-20px, 20px) scale(0.9) rotate(-5deg); }
                100% { transform: translate(0, 0) scale(1) rotate(0deg); }
            }

            .menu-open { overflow: hidden; }
        </style>
    </head>
    <body class="min-h-screen overflow-x-hidden relative">

        <!-- Background Decoration Vectors -->
        <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden" aria-hidden="true">
            <svg class="blob absolute -top-[10%] -right-[10%] w-[600px] h-[600px] duration-[25s]" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path fill="#FFB347" d="M44.7,-76.4C58.1,-69.2,69.2,-58.1,76.4,-44.7C83.7,-31.3,87.1,-15.7,85.1,-0.1C83.2,15.4,75.9,30.8,66.1,43.7C56.3,56.6,44.1,66.9,30.4,73.4C16.8,79.9,1.7,82.5,-13.7,79.9C-29.2,77.3,-45,69.5,-57.4,57.4C-69.8,45.3,-78.9,29,-82.1,12.2C-85.4,-4.6,-82.8,-21.8,-75,-36.5C-67.1,-51.2,-54,-63.3,-39.6,-70C-25.2,-76.7,-12.6,-78,1.4,-80.4C15.4,-82.8,31.3,-83.6,44.7,-76.4Z" transform="translate(100 100)" />
            </svg>
            <svg class="blob absolute -bottom-[15%] -left-[10%] w-[600px] h-[600px] duration-[30s]" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path fill="#FF7E5F" d="M38.1,-65.4C49.9,-58.7,60.1,-48.5,67.4,-36.2C74.8,-23.9,79.2,-9.5,78.5,4.6C77.8,18.7,72.1,32.4,63.1,44C54.1,55.5,41.9,64.8,28.7,70.5C15.5,76.1,1.4,78.1,-12.9,76.1C-27.1,74.1,-41.4,68.2,-53.4,58.7C-65.4,49.2,-75.1,36,-79.8,21.1C-84.5,6.2,-84.1,-10.4,-78.4,-24.9C-72.7,-39.4,-61.7,-51.8,-48.9,-58.1C-36.1,-64.4,-21.6,-64.7,-7.1,-63.5C7.4,-62.3,26.2,-72,38.1,-65.4Z" transform="translate(100 100)" />
            </svg>
            <svg class="blob absolute top-[40%] left-[15%] w-[400px] h-[400px] opacity-10 duration-[20s]" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path fill="#60A5FA" d="M36.7,-64.2C47.3,-57.6,55.4,-47.5,61.1,-36.2C66.8,-24.9,70.1,-12.5,71.1,0.6C72.1,13.7,70.9,27.5,65.2,39.6C59.5,51.7,49.4,62.1,37.3,67.6C25.2,73.1,12.6,73.8,-0.1,74C-12.8,74.2,-25.6,73.9,-37.4,68.7C-49.2,63.5,-60.1,53.4,-67.2,40.9C-74.4,28.4,-77.8,13.5,-77.4,-1.2C-77,-15.9,-72.8,-30.5,-64.5,-42.6C-56.2,-54.7,-43.8,-64.4,-30.9,-69.5C-18,-74.6,-4,-75.1,8.3,-73.4C20.6,-71.7,26.1,-70.7,36.7,-64.2Z" transform="translate(100 100)" />
            </svg>
        </div>

        <!-- Main App Layout -->
        <div class="min-h-screen flex flex-col">

            <!-- Navigation -->
            <nav class="bg-white/60 backdrop-blur-[25px] border-b border-white/40 sticky top-0 z-[1000]">
                <div class="max-w-[1200px] mx-auto px-6 h-[72px] flex items-center justify-between">
                    <div class="flex items-center gap-3 font-extrabold text-xl text-[#0F172A] tracking-tight">
                        <img src="/assets/images/logo.jpg" alt="Logo" class="h-[50px] object-contain">
                    </div>

                    <div class="hidden md:flex gap-3">
                        <a href="/index.php" class="nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
                        <a href="/modules/stores/list.php" class="nav-item <?php echo $activePage === 'stores' ? 'active' : ''; ?>">Stores</a>
                        <a href="/modules/products/list.php" class="nav-item <?php echo $activePage === 'products' ? 'active' : ''; ?>">Products</a>
                        <a href="/modules/prices/add.php" class="nav-item <?php echo $activePage === 'add-price' ? 'active' : ''; ?>">Add Price</a>
                        <a href="/modules/prices/history.php" class="nav-item <?php echo $activePage === 'history' ? 'active' : ''; ?>">History</a>
                        <a href="/logout.php" class="nav-item text-red-600">Logout</a>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button class="md:hidden flex flex-col gap-1 w-6 h-6 justify-center items-center mobile-menu-btn" aria-label="Toggle Menu">
                        <span class="bar w-full h-0.5 bg-[#0F172A] transition-all"></span>
                        <span class="bar w-full h-0.5 bg-[#0F172A] transition-all"></span>
                        <span class="bar w-full h-0.5 bg-[#0F172A] transition-all"></span>
                    </button>
                </div>

                <!-- Mobile Navigation Backdrop -->
                <div class="mobile-nav-backdrop fixed inset-0 bg-black/20 backdrop-blur-sm z-[998] hidden opacity-0 transition-opacity duration-300"></div>

                <!-- Mobile Navigation Drawer -->
                <div class="mobile-nav fixed top-0 right-0 h-full w-[280px] bg-white/95 backdrop-blur-[25px] shadow-2xl z-[999] transform translate-x-full transition-transform duration-300 flex flex-col p-6">
                    <button class="drawer-close-btn self-end text-3xl text-[#0F172A] mb-8">&times;</button>
                    <a href="/index.php" class="mobile-nav-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
                    <a href="/modules/stores/list.php" class="mobile-nav-item <?php echo $activePage === 'stores' ? 'active' : ''; ?>">Stores</a>
                    <a href="/modules/products/list.php" class="mobile-nav-item <?php echo $activePage === 'products' ? 'active' : ''; ?>">Products</a>
                    <a href="/modules/prices/add.php" class="mobile-nav-item <?php echo $activePage === 'add-price' ? 'active' : ''; ?>">Add Price</a>
                    <a href="/modules/prices/history.php" class="mobile-nav-item <?php echo $activePage === 'history' ? 'active' : ''; ?>">History</a>
                    <a href="/logout.php" class="mobile-nav-item text-red-600">Logout</a>
                </div>
            </nav>

            <!-- Main Content Area -->
            <main class="flex-1 py-10 px-6 max-w-[1200px] w-full mx-auto">
    <?php
}
