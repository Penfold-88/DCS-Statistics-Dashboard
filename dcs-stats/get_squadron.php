<?php
// === Security: Simple check (improve as needed) ===
// Example: only allow requests from your domain (optional)
if (
    !isset($_SERVER['HTTP_REFERER']) || 
    strpos($_SERVER['HTTP_REFERER'], 'skypirates.uk') === false
) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized request"]);
    exit;
}

// Whitelist allowed files
$allowed_files = [
    'squadrons',
    'squadron_members',
    'players',
    'squadron_credits'
];

// Validate 'file' parameter
if (!isset($_GET['file']) || !in_array($_GET['file'], $allowed_files, true)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid file request"]);
    exit;
}

// Build file path
$path = __DIR__ . "/data/{$_GET['file']}.json";

// Check file existence
if (!file_exists($path)) {
    http_response_code(404);
    echo json_encode(["error" => "File not found"]);
    exit;
}

// Return file content as JSON
header('Content-Type: application/json');
readfile($path);
