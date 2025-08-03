<?php
// Simple maintenance mode page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Maintenance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .maintenance-container {
            text-align: center;
            color: #333;
        }
        .maintenance-container img {
            width: 100px;
            height: 100px;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><line x1='10' y1='10' x2='90' y2='90' stroke='red' stroke-width='10'/><line x1='90' y1='10' x2='10' y2='90' stroke='red' stroke-width='10'/></svg>" alt="Red X" />
        <h1>Statistics is Temporarily Unavailable</h1>
        <p>Please try again later.</p>
    </div>
</body>
</html>
