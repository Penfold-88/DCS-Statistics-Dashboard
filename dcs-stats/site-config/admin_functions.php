<?php
/**
 * Admin Panel Helper Functions
 */

// Ensure admin panel constant is defined
if (!defined('ADMIN_PANEL')) {
    die('Direct access not permitted');
}

/**
 * Get current admin user
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    $users = getAdminUsers();
    foreach ($users as $user) {
        if ($user['id'] == $_SESSION['admin_id']) {
            return $user;
        }
    }
    
    return null;
}

/**
 * Format date for display
 */
function formatDate($date, $format = null) {
    if (!$date) return 'Never';
    if (!$format) $format = 'M d, Y H:i';
    $timestamp = strtotime($date);
    if (!$timestamp) return 'Never';
    return date($format, $timestamp);
}

/**
 * Normalize older and newer admin log formats into one shape.
 */
function normalizeAdminLog($log) {
    $log = is_array($log) ? $log : [];
    $createdAt = $log['created_at'] ?? $log['timestamp'] ?? null;

    return array_merge([
        'id' => null,
        'admin_id' => 0,
        'action' => 'UNKNOWN',
        'target_type' => null,
        'target_id' => null,
        'details' => null,
        'ip_address' => $log['ip'] ?? 'unknown',
        'user_agent' => 'unknown',
        'created_at' => $createdAt
    ], $log, [
        'created_at' => $createdAt,
        'ip_address' => $log['ip_address'] ?? $log['ip'] ?? 'unknown'
    ]);
}

function adminLogTimestamp($log) {
    $createdAt = $log['created_at'] ?? $log['timestamp'] ?? null;
    $timestamp = $createdAt ? strtotime($createdAt) : 0;
    return $timestamp ?: 0;
}

/**
 * Log admin action
 */
function logAdminAction($action, $details = []) {
    $logFile = ADMIN_LOGS_FILE;
    $logs = [];
    
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true) ?: [];
    }
    
    $logs[] = [
        'action' => $action,
        'admin_id' => $_SESSION['admin_id'] ?? 0,
        'admin_username' => getCurrentAdmin()['username'] ?? 'System',
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'created_at' => date(DATE_FORMAT)
    ];
    
    // Keep only last 1000 logs
    if (count($logs) > 1000) {
        $logs = array_slice($logs, -1000);
    }
    
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    @chmod($logFile, 0600);
}

/**
 * Get player data from API
 */
function getPlayers($search = null, $limit = null, $offset = 0) {
    require_once dirname(__DIR__) . '/api_client_enhanced.php';
    
    try {
        $client = createEnhancedAPIClient();
        
        // If searching, use the search endpoint
        if ($search) {
            $searchUrl = '/search_players.php?search=' . urlencode($search);
            if ($limit) {
                $searchUrl .= '&limit=' . $limit;
            }
            $response = @file_get_contents(dirname(__DIR__) . $searchUrl);
            if ($response) {
                $data = json_decode($response, true);
                return $data['results'] ?? [];
            }
        }
        
        // Otherwise get all players from topkills endpoint
        $players = $client->request('/topkills', null, 'GET');
        
        if ($players && is_array($players)) {
            // Apply offset and limit manually
            if ($offset || $limit) {
                return array_slice($players, $offset, $limit);
            }
            return $players;
        }
    } catch (Exception $e) {
        // Return empty array on error
    }
    
    return [];
}

/**
 * Get player statistics
 */
function getPlayerStats($ucid) {
    require_once dirname(__DIR__) . '/api_client_enhanced.php';
    
    $stats = [
        'ucid' => $ucid,
        'kills' => 0,
        'deaths' => 0,
        'flight_hours' => 0,
        'sorties' => 0,
        'last_seen' => null,
        'kd_ratio' => 0
    ];
    
    try {
        $client = createEnhancedAPIClient();
        
        // Get player stats from API
        $playerData = $client->request('/stats', ['ucid' => $ucid]);
        
        if ($playerData && is_array($playerData) && !empty($playerData)) {
            $player = is_array($playerData[0]) ? $playerData[0] : $playerData;
            
            $stats['kills'] = intval($player['kills'] ?? 0);
            $stats['deaths'] = intval($player['deaths'] ?? 0);
            $stats['sorties'] = intval($player['sorties'] ?? 0);
            $stats['flight_hours'] = floatval($player['flight_hours'] ?? 0);
            $stats['last_seen'] = $player['date'] ?? $player['last_seen'] ?? null;
            
            // Calculate K/D ratio
            $stats['kd_ratio'] = $stats['deaths'] > 0 ? 
                round($stats['kills'] / $stats['deaths'], 2) : 
                $stats['kills'];
        }
    } catch (Exception $e) {
        // Return default stats on error
    }
    
    return $stats;
}

/**
 * Get player bans
 */
function getPlayerBans($activeOnly = true) {
    $bans = json_decode(file_get_contents(ADMIN_BANS_FILE), true) ?: [];
    
    if ($activeOnly) {
        $bans = array_filter($bans, function($ban) {
            if (!$ban['is_active']) return false;
            if (!$ban['expires_at']) return true;
            return strtotime($ban['expires_at']) > time();
        });
    }
    
    return array_values($bans);
}

/**
 * Check if player is banned
 */
function isPlayerBanned($ucid) {
    $bans = getPlayerBans(true);
    
    foreach ($bans as $ban) {
        if ($ban['player_ucid'] === $ucid) {
            return true;
        }
    }
    
    return false;
}


/**
 * Get recent admin activity
 */
function getRecentActivity($limit = 10) {
    $logs = json_decode(@file_get_contents(ADMIN_LOGS_FILE), true) ?: [];
    $logs = array_map('normalizeAdminLog', $logs);
    
    // Sort by date descending
    usort($logs, function($a, $b) {
        return adminLogTimestamp($b) - adminLogTimestamp($a);
    });
    
    // Get admin usernames
    $users = getAdminUsers();
    $userMap = [];
    foreach ($users as $user) {
        $userMap[$user['id']] = $user['username'];
    }
    
    // Add username to logs
    foreach ($logs as &$log) {
        $log['admin_username'] = $userMap[$log['admin_id']] ?? 'Unknown';
    }
    
    return array_slice($logs, 0, $limit);
}

/**
 * Get dashboard statistics
 */
function getDashboardStats() {
    $stats = [
        'total_players' => 0,
        'active_players_24h' => 0,
        'active_players_7d' => 0,
        'total_bans' => 0,
        'total_admins' => 0,
        'recent_activity' => []
    ];
    
    // Count players
    $players = getPlayers();
    $stats['total_players'] = count($players);
    
    // Count active players (would need to check mission stats)
    $now = time();
    $day_ago = $now - 86400;
    $week_ago = $now - 604800;
    
    // Count bans
    $bans = getPlayerBans(true);
    $stats['total_bans'] = count($bans);
    
    // Count admins
    $users = getAdminUsers();
    $stats['total_admins'] = count(array_filter($users, function($u) {
        return $u['is_active'];
    }));
    
    // Get recent activity
    $stats['recent_activity'] = getRecentActivity(5);
    
    return $stats;
}

/**
 * Export data to CSV
 */
function exportToCSV($data, $filename = 'export.csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
    }
    
    // Add data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

/**
 * Export data to JSON
 */
function exportToJSON($data, $filename = 'export.json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Sanitize output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get role badge HTML
 */
function getRoleBadge($role) {
    $roleNames = [
        ROLE_AIR_BOSS => ['name' => 'Air Boss', 'color' => '#ff4444', 'icon' => '✈️'],
        ROLE_LSO => ['name' => 'LSO', 'color' => '#2196F3', 'icon' => '🚦']
    ];
    
    $info = $roleNames[$role] ?? ['name' => 'Unknown', 'color' => '#666', 'icon' => '❓'];
    
    return '<span style="display: inline-block; padding: 4px 8px; background-color: ' . 
           $info['color'] . '; color: white; border-radius: 3px; font-size: 12px; font-weight: bold;">' . 
           $info['icon'] . ' ' . $info['name'] . '</span>';
}

/**
 * Generate pagination HTML
 */
function getPagination($totalItems, $perPage, $currentPage, $baseUrl) {
    $totalPages = ceil($totalItems / $perPage);
    if ($totalPages <= 1) return '';
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="pagination-prev">Previous</a>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<span class="pagination-current">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $baseUrl . '?page=' . $i . '" class="pagination-link">' . $i . '</a>';
        }
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="pagination-next">Next</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Load maintenance configuration
 */
function loadMaintenanceConfig() {
    $file = __DIR__ . '/data/maintenance.json';
    $defaults = ['enabled' => false, 'ip_whitelist' => []];

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            return array_merge($defaults, $data);
        }
    }

    return $defaults;
}

/**
 * Save maintenance configuration
 */
function saveMaintenanceConfig($config) {
    $file = __DIR__ . '/data/maintenance.json';
    return file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT)) !== false;
}
