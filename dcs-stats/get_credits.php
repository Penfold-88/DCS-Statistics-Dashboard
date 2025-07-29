<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

// Include security functions
require_once __DIR__ . '/security_functions.php';

// Rate limiting: 60 requests per minute
if (!checkRateLimit(60, 60)) {
    exit;
}

$playersFile = 'data/players.json';
$creditsFile = 'data/credits.json';

// Check if files exist
if (!file_exists($playersFile) || !file_exists($creditsFile)) {
    echo json_encode([]);
    exit;
}

$players = [];
$handle = fopen($playersFile, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $data = json_decode($line, true);
        if (isset($data['ucid'], $data['name'])) {
            $players[$data['ucid']] = $data['name'];
        }
    }
    fclose($handle);
}

$credits = [];
$handle = fopen($creditsFile, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $data = json_decode($line, true);
        $ucid = $data['player_ucid'];
        $points = $data['points'];
        if (!isset($credits[$ucid])) {
            $credits[$ucid] = 0;
        }
        $credits[$ucid] += $points;
    }
    fclose($handle);
}

$result = [];
foreach ($credits as $ucid => $totalPoints) {
    $result[] = [
        "name" => $players[$ucid] ?? $ucid,
        "credits" => $totalPoints
    ];
}

usort($result, function($a, $b) {
    return $b['credits'] - $a['credits'];
});

// Sanitize data before sending to prevent XSS
foreach ($result as &$item) {
    $item['name'] = htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8');
}

echo json_encode($result);
?>
