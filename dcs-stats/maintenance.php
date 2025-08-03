<?php
/**
 * Maintenance mode page
 */

// Ensure helper functions are available when accessed directly
require_once __DIR__ . '/config_path.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance</title>
    <link rel="stylesheet" href="<?php echo url('styles.php'); ?>">
    <link rel="stylesheet" href="<?php echo url('styles-mobile.css'); ?>">
</head>
<body>
    <main class="maintenance-page">
        <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><line x1='10' y1='10' x2='90' y2='90' stroke='red' stroke-width='10'/><line x1='90' y1='10' x2='10' y2='90' stroke='red' stroke-width='10'/></svg>" alt="Red X" class="maintenance-icon">
        <h1>Statistics is Temporarily Unavailable</h1>
        <p>Please try again later.</p>
    </main>
</body>
</html>
