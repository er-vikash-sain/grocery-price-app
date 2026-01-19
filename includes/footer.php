<?php

declare(strict_types=1);

function render_footer(): void
{
    ?>
        </main>
    </div>
    <script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/sw.js');
}
</script>

    </body>
    </html>
    <?php
}

