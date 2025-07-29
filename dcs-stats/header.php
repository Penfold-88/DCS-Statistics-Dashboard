<?php
// Security headers for protection against common web vulnerabilities
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self';");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DCS Statistics Dashboard</title>
  <link rel="stylesheet" href="/DCS-Stats-Demo/dev/styles.css" />
  <script>
    // XSS Protection function
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }
  </script>
</head>
<body>
  <header>
    <div class="logo">
      <img src="jet.png" alt="Jet Logo" style="height: 40px; vertical-align: middle; margin-right: 10px;" />
      <h1 style="display: inline-block; margin: 0;">DCS Statistics Dashboard</h1>
    </div>
  </header>
