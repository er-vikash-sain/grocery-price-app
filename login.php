<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

start_session_if_needed();

if (current_user_id() !== null) {
    redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = 'Email and password are required.';
        } else {
            $db = get_db();
            $stmt = $db->prepare('SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $error = 'Invalid email or password.';
            } else {
                login_user($user);
                $redirect = $_GET['redirect'] ?? '/index.php';
                redirect($redirect);
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Login - Grocery Price App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- PWA Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#FFF9F2">

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
            opacity: 0.3;
            animation: float 20s infinite alternate cubic-bezier(0.45, 0, 0.55, 1);
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            33% { transform: translate(30px, -50px) scale(1.1) rotate(5deg); }
            66% { transform: translate(-20px, 20px) scale(0.9) rotate(-5deg); }
            100% { transform: translate(0, 0) scale(1) rotate(0deg); }
        }

        .custom-checkbox:checked + .checkbox-box {
            background-color: #FF7E5F;
        }

        .checkbox-box:after {
            content: "";
            position: absolute;
            display: none;
            left: 5px;
            top: 1px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .custom-checkbox:checked + .checkbox-box:after {
            display: block;
        }

        .btn-premium {
            background: linear-gradient(135deg, #FFB347 0%, #FF7E5F 100%);
            box-shadow: 0 10px 20px rgba(255, 126, 95, 0.3), 0 0 20px rgba(255, 179, 71, 0.2);
        }

        .btn-premium:hover {
            box-shadow: 0 15px 25px rgba(255, 126, 95, 0.4), 0 0 30px rgba(255, 179, 71, 0.3);
        }

        .input-focus-ring:focus {
            border-color: #FFB347;
            box-shadow: 0 0 0 4px rgba(255, 179, 71, 0.1);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-6 relative overflow-x-hidden">

    <!-- Background Decoration Vectors -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
        <svg class="blob absolute -top-[10%] -right-[10%] w-[600px] h-[600px] duration-[25s]" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <path fill="#FFB347" d="M44.7,-76.4C58.1,-69.2,69.2,-58.1,76.4,-44.7C83.7,-31.3,87.1,-15.7,85.1,-0.1C83.2,15.4,75.9,30.8,66.1,43.7C56.3,56.6,44.1,66.9,30.4,73.4C16.8,79.9,1.7,82.5,-13.7,79.9C-29.2,77.3,-45,69.5,-57.4,57.4C-69.8,45.3,-78.9,29,-82.1,12.2C-85.4,-4.6,-82.8,-21.8,-75,-36.5C-67.1,-51.2,-54,-63.3,-39.6,-70C-25.2,-76.7,-12.6,-78,1.4,-80.4C15.4,-82.8,31.3,-83.6,44.7,-76.4Z" transform="translate(100 100)" />
        </svg>
        <svg class="blob absolute -bottom-[15%] -left-[10%] w-[600px] h-[600px] duration-[30s]" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <path fill="#FF7E5F" d="M38.1,-65.4C49.9,-58.7,60.1,-48.5,67.4,-36.2C74.8,-23.9,79.2,-9.5,78.5,4.6C77.8,18.7,72.1,32.4,63.1,44C54.1,55.5,41.9,64.8,28.7,70.5C15.5,76.1,1.4,78.1,-12.9,76.1C-27.1,74.1,-41.4,68.2,-53.4,58.7C-65.4,49.2,-75.1,36,-79.8,21.1C-84.5,6.2,-84.1,-10.4,-78.4,-24.9C-72.7,-39.4,-61.7,-51.8,-48.9,-58.1C-36.1,-64.4,-21.6,-64.7,-7.1,-63.5C7.4,-62.3,26.2,-72,38.1,-65.4Z" transform="translate(100 100)" />
        </svg>
    </div>

    <div class="w-full max-w-[440px] z-10 animate-[fadeIn_0.6s_ease-out]">
        <div class="bg-[#fef9f3] backdrop-blur-[20px] border border-white/80 rounded-[24px] p-10 md:p-14 shadow-[0_20px_40px_rgba(0,0,0,0.04)]">

            <div class="text-center mb-10">
                <div class="mb-6 flex justify-center">
                    <img src="/public/assets/images/logo.jpg" alt="Logo" class="h-[90px] w-auto object-contain">
                </div>
                <h1 class="text-3xl font-extrabold text-[#0F172A] mb-2 tracking-tight">Welcome Back</h1>
                <p class="text-[#475569] text-base">Please provide your details below</p>

                <?php if ($error !== ''): ?>
                    <div class="mt-6 p-4 bg-red-50/50 border border-red-100 rounded-xl text-red-600 text-sm font-medium animate-pulse">
                        <?php echo sanitize($error); ?>
                    </div>
                <?php endif; ?>
            </div>

            <form class="space-y-5" method="POST">
                <?php echo csrf_field(); ?>
                <div class="relative flex items-center group">
                    <span class="absolute left-4 text-[#94A3B8] group-focus-within:text-[#FFB347] transition-colors duration-200">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22 6 12 13 2 6"></polyline></svg>
                    </span>
                    <input type="email" id="email" name="email" placeholder="Enter Your Email" required
                        class="w-full pl-12 pr-4 py-4 bg-white border border-[#E2E8F0] rounded-lg outline-none transition-all input-focus-ring text-base text-[#0F172A] placeholder-[#94A3B8]">
                </div>

                <div class="relative flex items-center group">
                    <span class="absolute left-4 text-[#94A3B8] group-focus-within:text-[#FFB347] transition-colors duration-200">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </span>
                    <input type="password" id="password" name="password" placeholder="Enter Your Password" required
                        class="w-full pl-12 pr-12 py-4 bg-white border border-[#E2E8F0] rounded-lg outline-none transition-all input-focus-ring text-base text-[#0F172A] placeholder-[#94A3B8]">
                    <span class="absolute right-4 text-[#94A3B8] hover:text-[#64748B] cursor-pointer transition-colors duration-200" id="password-toggle">
                        <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <svg class="icon-eye-off hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                    </span>
                </div>

                <div class="flex items-center justify-between text-sm font-semibold mb-8">
                    <label class="flex items-center gap-2 cursor-pointer text-[#475569] select-none">
                        <input type="checkbox" name="remember" class="custom-checkbox absolute opacity-0 cursor-pointer h-0 w-0">
                        <span class="checkbox-box relative h-[18px] w-[18px] border border-[#FF7E5F] rounded transition-all"></span>
                        <span>Remember me</span>
                    </label>
                    <a href="/forgot-password.php" class="text-[#FF7E5F] hover:text-[#FFB347] transition-colors">Forgot Password?</a>
                </div>

                <div>
                    <button type="submit" class="w-full py-[18px] btn-premium text-white rounded-lg font-bold text-base transition-all hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]">
                        Log In
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center text-sm text-[#475569]">
                Don't have an account? <a href="/signup.php" class="text-[#FF7E5F] font-bold ml-1 hover:text-[#FFB347] transition-colors">Sign Up</a>
            </div>

        </div>
    </div>

    <script>
        // Password Visibility Toggle
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('password-toggle');
        const eyeIcon = toggleBtn.querySelector('.icon-eye');
        const eyeOffIcon = toggleBtn.querySelector('.icon-eye-off');

        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

            eyeIcon.classList.toggle('hidden');
            eyeOffIcon.classList.toggle('hidden');
            toggleBtn.style.color = isPassword ? '#FF7E5F' : '#94A3B8';
        });
    </script>
</body>
</html>
