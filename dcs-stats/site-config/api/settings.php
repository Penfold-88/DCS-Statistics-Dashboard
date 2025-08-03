<?php
/**
 * Settings API Endpoint
 * Handles retrieval and updates of site settings
 */

require_once dirname(__DIR__) . '/auth.php';
require_once dirname(__DIR__) . '/admin_functions.php';
require_once dirname(dirname(__DIR__)) . '/site_features.php';
require_once dirname(dirname(__DIR__)) . '/security_functions.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check permissions
if (!hasPermission('change_settings')) {
    http_response_code(403);
    echo json_encode(['error' => 'Insufficient permissions']);
    exit;
}

// Handle request based on method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get current settings
        $features = loadSiteFeatures();
        $groups = getFeatureGroups();
        $dependencies = getFeatureDependencies();
        
        echo json_encode([
            'success' => true,
            'features' => $features,
            'groups' => $groups,
            'dependencies' => $dependencies
        ]);
        break;
        
    case 'POST':
    case 'PUT':
        // Update settings
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['features']) || !is_array($input['features'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request data']);
            exit;
        }
        
        // Load current settings first to preserve custom settings not in groups
        $allFeatures = loadSiteFeatures();
        
        // Update only the features that are in groups
        foreach (getFeatureGroups() as $group => $features) {
            foreach ($features as $key => $label) {
                $allFeatures[$key] = isset($input['features'][$key]) && $input['features'][$key] === true;
            }
        }
        
        // Preserve custom settings that aren't in feature groups
        if (isset($input['features']['show_discord_link'])) {
            $allFeatures['show_discord_link'] = (bool)$input['features']['show_discord_link'];
        }
        if (isset($input['features']['show_squadron_homepage'])) {
            $allFeatures['show_squadron_homepage'] = (bool)$input['features']['show_squadron_homepage'];
        }
        if (isset($input['features']['discord_link_url'])) {
            $allFeatures['discord_link_url'] = $input['features']['discord_link_url'];
        }
        if (isset($input['features']['squadron_homepage_url'])) {
            $allFeatures['squadron_homepage_url'] = $input['features']['squadron_homepage_url'];
        }
        if (isset($input['features']['squadron_homepage_text'])) {
            $allFeatures['squadron_homepage_text'] = $input['features']['squadron_homepage_text'];
        }
        if (isset($input['features']['maintenance_whitelist'])) {
            $allFeatures['maintenance_whitelist'] = $input['features']['maintenance_whitelist'];
        }
        
        // Apply dependencies
        $dependencies = getFeatureDependencies();
        foreach ($dependencies as $parent => $children) {
            if (!$allFeatures[$parent]) {
                foreach ($children as $child) {
                    $allFeatures[$child] = false;
                }
            }
        }
        
        // Save settings
        if (saveSiteFeatures($allFeatures)) {
            // Log the action
            $currentAdmin = getCurrentAdmin();
            logAdminActivity('SETTINGS_CHANGE', $currentAdmin['id'], 'settings', 'site_features', $allFeatures);
            
            echo json_encode([
                'success' => true,
                'message' => 'Settings updated successfully',
                'features' => $allFeatures
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save settings']);
        }
        break;
        
    case 'PATCH':
        // Toggle single feature
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['feature']) || !isset($input['enabled'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Feature and enabled status required']);
            exit;
        }
        
        $features = loadSiteFeatures();
        
        // Check if feature exists
        $validFeature = false;
        foreach (getFeatureGroups() as $group => $groupFeatures) {
            if (isset($groupFeatures[$input['feature']])) {
                $validFeature = true;
                break;
            }
        }
        
        if (!$validFeature) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid feature']);
            exit;
        }
        
        // Update feature
        $features[$input['feature']] = (bool)$input['enabled'];
        
        // Apply dependencies if disabling
        if (!$input['enabled']) {
            $dependencies = getFeatureDependencies();
            if (isset($dependencies[$input['feature']])) {
                foreach ($dependencies[$input['feature']] as $child) {
                    $features[$child] = false;
                }
            }
        }
        
        // Save settings
        if (saveSiteFeatures($features)) {
            $currentAdmin = getCurrentAdmin();
            logAdminActivity('SETTINGS_CHANGE', $currentAdmin['id'], 'settings', $input['feature'], $input['enabled']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Feature toggled successfully',
                'feature' => $input['feature'],
                'enabled' => $features[$input['feature']],
                'features' => $features
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update feature']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}