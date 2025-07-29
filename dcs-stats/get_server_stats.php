<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

// Include security functions
require_once __DIR__ . '/security_functions.php';

// Rate limiting
if (!checkRateLimit(60, 60)) {
    exit;
}

// Validate file paths
$dataDir = __DIR__ . '/data';
$playersFile = validatePath($dataDir . '/players.json', $dataDir);
$missionsFile = validatePath($dataDir . '/missionstats.json', $dataDir);
$squadronsFile = validatePath($dataDir . '/squadrons.json', $dataDir);
$squadronMembersFile = validatePath($dataDir . '/squadron_members.json', $dataDir);

if (!$playersFile || !$missionsFile) {
    logSecurityEvent('PATH_TRAVERSAL_ATTEMPT', 'Invalid file path access attempt');
    echo json_encode(["error" => "Service temporarily unavailable"]);
    exit;
}

// Initialize statistics
$totalPlayers = 0;
$playerVisits = [];
$totalKills = 0;
$totalDeaths = 0;
$pilotStats = [];
$squadronStats = [];
$playerSquadronMap = [];

// Count unique players
if (file_exists($playersFile)) {
    $handle = fopen($playersFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $entry = validateJsonLine($line, ['name', 'ucid']);
            if (!$entry) continue;
            
            $totalPlayers++;
            $pilotStats[$entry['ucid']] = [
                'name' => $entry['name'],
                'visits' => 0,
                'kills' => 0,
                'deaths' => 0
            ];
        }
        fclose($handle);
    }
}

// Process mission stats
if (file_exists($missionsFile)) {
    $handle = fopen($missionsFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $entry = validateJsonLine($line, ['event']);
            if (!$entry) continue;
            
            // Count visits based on takeoffs (actual server activity)
            if ($entry['event'] === "S_EVENT_TAKEOFF" && isset($entry['init_id']) && isset($pilotStats[$entry['init_id']])) {
                $pilotStats[$entry['init_id']]['visits']++;
            }
            
            // Count kills
            if ($entry['event'] === "S_EVENT_HIT" && isset($entry['init_id']) && isset($pilotStats[$entry['init_id']])) {
                $pilotStats[$entry['init_id']]['kills']++;
                $totalKills++;
            }
            
            // Count deaths (crashes, ejections, and pilot deaths count as deaths)
            if (in_array($entry['event'], ["S_EVENT_CRASH", "S_EVENT_EJECTION", "S_EVENT_PILOT_DEAD"]) && 
                isset($entry['init_id']) && isset($pilotStats[$entry['init_id']])) {
                $pilotStats[$entry['init_id']]['deaths']++;
                $totalDeaths++;
            }
            
            // Also count when a pilot is killed by another player
            if ($entry['event'] === "S_EVENT_KILL" && isset($entry['target_id']) && 
                isset($pilotStats[$entry['target_id']]) && $entry['target_cat'] === "Airplanes") {
                $pilotStats[$entry['target_id']]['deaths']++;
                $totalDeaths++;
            }
        }
        fclose($handle);
    }
}

// No fallback needed - we're counting actual takeoffs as visits

// Get top 5 pilots by visits
$pilotsByVisits = $pilotStats;
usort($pilotsByVisits, function($a, $b) {
    return $b['visits'] - $a['visits'];
});
$top5Pilots = array_slice($pilotsByVisits, 0, 5);

// Format top 5 for chart
$top5Data = [];
foreach ($top5Pilots as $pilot) {
    if ($pilot['visits'] > 0) {
        $top5Data[] = [
            'name' => htmlspecialchars($pilot['name'], ENT_QUOTES, 'UTF-8'),
            'visits' => $pilot['visits']
        ];
    }
}

// Load squadron data
$squadrons = [];
if (file_exists($squadronsFile)) {
    $handle = fopen($squadronsFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $entry = validateJsonLine($line, ['id', 'name']);
            if (!$entry) continue;
            
            $squadrons[$entry['id']] = [
                'name' => $entry['name'],
                'image_url' => $entry['image_url'] ?? null
            ];
            $squadronStats[$entry['id']] = [
                'name' => $entry['name'],
                'image_url' => $entry['image_url'] ?? null,
                'visits' => 0
            ];
        }
        fclose($handle);
    }
}

// Map players to squadrons
if (file_exists($squadronMembersFile)) {
    $handle = fopen($squadronMembersFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $entry = validateJsonLine($line, ['player_ucid', 'squadron_id']);
            if (!$entry) continue;
            
            $playerSquadronMap[$entry['player_ucid']] = $entry['squadron_id'];
        }
        fclose($handle);
    }
}

// Calculate squadron visits based on player visits
foreach ($pilotStats as $ucid => $stats) {
    if (isset($playerSquadronMap[$ucid]) && isset($squadronStats[$playerSquadronMap[$ucid]])) {
        $squadronId = $playerSquadronMap[$ucid];
        $squadronStats[$squadronId]['visits'] += $stats['visits'];
    }
}

// Get top 3 squadrons by visits
$squadronsByVisits = array_values($squadronStats);
usort($squadronsByVisits, function($a, $b) {
    return $b['visits'] - $a['visits'];
});
$top3Squadrons = array_slice($squadronsByVisits, 0, 3);

// Format top 3 squadrons for chart
$top3SquadronsData = [];
foreach ($top3Squadrons as $squadron) {
    if ($squadron['visits'] > 0) {
        $top3SquadronsData[] = [
            'name' => htmlspecialchars($squadron['name'], ENT_QUOTES, 'UTF-8'),
            'visits' => $squadron['visits'],
            'image_url' => $squadron['image_url'] ? htmlspecialchars($squadron['image_url'], ENT_QUOTES, 'UTF-8') : null
        ];
    }
}

// Return stats
echo json_encode([
    'totalPlayers' => $totalPlayers,
    'totalKills' => $totalKills,
    'totalDeaths' => $totalDeaths,
    'top5Pilots' => $top5Data,
    'top3Squadrons' => $top3SquadronsData
]);