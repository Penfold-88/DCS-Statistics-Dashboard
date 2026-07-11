<?php

namespace DcsStats\Core;

final class AdminSetupProgressRenderer
{
    public function show(): void
    {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Setting Up Admin Panel</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: #1a1a1a;
                    color: #fff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    margin: 0;
                }
                .setup-container {
                    background: #2d2d2d;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.5);
                    text-align: center;
                    max-width: 500px;
                }
                h1 {
                    color: #4CAF50;
                    margin-bottom: 20px;
                }
                .spinner {
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid #4CAF50;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    animation: spin 1s linear infinite;
                    margin: 20px auto;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .status {
                    margin: 15px 0;
                    padding: 10px;
                    background: #1a1a1a;
                    border-radius: 5px;
                }
                .success { color: #4CAF50; }
                .error { color: #f44336; }
            </style>
            <meta http-equiv="refresh" content="3">
        </head>
        <body>
            <div class="setup-container">
                <h1>Setting Up Your Environment</h1>
                <div class="spinner"></div>
                <p>Please wait while we configure the admin panel...</p>
                <div class="status">Creating necessary directories and files...</div>
            </div>
        </body>
        </html>
        <?php
        flush();
        sleep(1);
    }
}
