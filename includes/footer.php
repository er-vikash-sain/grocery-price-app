<?php

declare(strict_types=1);

function render_footer(): void
{
    ?>
            </main>
        </div>

        <style>
            .nav-item {
                text-decoration: none;
                color: var(--text-muted);
                font-weight: 700;
                font-size: 14px;
                letter-spacing: -0.2px;
                transition: all 0.3s ease;
                padding: 10px 18px;
                border-radius: 8px;
            }

            .nav-item:hover,
            .nav-item.active {
                color: #FF7E5F;
                background-color: rgba(255, 126, 95, 0.05);
            }

            .nav-item.active {
                background-color: rgba(255, 126, 95, 0.1);
            }

            .mobile-nav-item {
                display: block;
                padding: 14px 20px;
                color: var(--text-main);
                font-weight: 600;
                font-size: 16px;
                text-decoration: none;
                border-radius: 8px;
                transition: all 0.2s ease;
                margin-bottom: 8px;
            }

            .mobile-nav-item:hover,
            .mobile-nav-item.active {
                background-color: rgba(255, 126, 95, 0.1);
                color: #FF7E5F;
            }

            .mobile-menu-btn.open .bar:nth-child(1) {
                transform: rotate(45deg) translate(5px, 5px);
            }

            .mobile-menu-btn.open .bar:nth-child(2) {
                opacity: 0;
            }

            .mobile-menu-btn.open .bar:nth-child(3) {
                transform: rotate(-45deg) translate(5px, -5px);
            }

            .mobile-nav.open {
                transform: translateX(0);
            }

            .mobile-nav-backdrop.open {
                display: block;
                opacity: 1;
            }
        </style>

        <script>
            // Mobile Menu Toggle
            const menuBtn = document.querySelector('.mobile-menu-btn');
            const mobileNav = document.querySelector('.mobile-nav');
            const closeBtn = document.querySelector('.drawer-close-btn');
            const backdrop = document.querySelector('.mobile-nav-backdrop');

            function toggleMenu() {
                mobileNav.classList.toggle('open');
                menuBtn.classList.toggle('open');
                backdrop.classList.toggle('open');
                document.body.classList.toggle('menu-open');
            }

            if (menuBtn) menuBtn.addEventListener('click', toggleMenu);
            if (closeBtn) closeBtn.addEventListener('click', toggleMenu);
            if (backdrop) backdrop.addEventListener('click', toggleMenu);

            // Service Worker Registration
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js');
            }
        </script>

    </body>
    </html>
    <?php
}

