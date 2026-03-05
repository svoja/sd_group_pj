<?php
$resolvedTitle = isset($pageTitle) && $pageTitle !== '' ? $pageTitle : 'ARAII MOTO';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($resolvedTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    },
                    colors: {
                        obsidian: {
                            bg: '#000000',
                            surface: '#ffffff',
                            edge: 'rgba(0, 0, 0, 0.24)',
                            muted: '#3f3f46'
                        },
                        premium: '#c1121f'
                    }
                }
            }
        };
    </script>

    <link rel="stylesheet" href="assets/css/app.css">

    <?php if (!empty($extraHead)): ?>
<?= $extraHead ?>
    <?php endif; ?>
</head>
