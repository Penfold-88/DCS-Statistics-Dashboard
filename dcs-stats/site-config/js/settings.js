/**
 * Settings Management JavaScript
 * Handles feature toggles via API
 */

class SettingsManager {
    constructor() {
        this.apiUrl = 'api/settings.php';
        this.features = {};
        this.dependencies = {};
        this.csrfToken = window.adminCsrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
    }
    
    /**
     * Load current settings from API
     */
    async loadSettings() {
        try {
            const response = await fetch(this.apiUrl);
            if (!response.ok) {
                throw new Error('Failed to load settings');
            }
            
            const data = await response.json();
            this.features = data.features;
            this.dependencies = data.dependencies;
            
            return data;
        } catch (error) {
            console.error('Error loading settings:', error);
            throw error;
        }
    }
    
    /**
     * Save all settings
     */
    async saveAllSettings(features) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                body: JSON.stringify({ features, csrf_token: this.csrfToken })
            });
            
            if (!response.ok) {
                throw new Error('Failed to save settings');
            }
            
            const data = await response.json();
            this.features = data.features;
            
            return data;
        } catch (error) {
            console.error('Error saving settings:', error);
            throw error;
        }
    }
    
    /**
     * Toggle a single feature
     */
    async toggleFeature(feature, enabled) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                },
                body: JSON.stringify({ feature, enabled, csrf_token: this.csrfToken })
            });
            
            if (!response.ok) {
                throw new Error('Failed to toggle feature');
            }
            
            const data = await response.json();
            this.features = data.features;
            
            return data;
        } catch (error) {
            console.error('Error toggling feature:', error);
            throw error;
        }
    }
    
    /**
     * Get dependent features that will be disabled
     */
    getDependentFeatures(feature) {
        return this.dependencies[feature] || [];
    }
    
    /**
     * Check if a feature has dependencies
     */
    hasDependencies(feature) {
        return this.dependencies[feature] && this.dependencies[feature].length > 0;
    }
}

// Example usage in settings.php:
// const settingsManager = new SettingsManager();
// 
// // Load settings on page load
// settingsManager.loadSettings().then(data => {
//     // Update UI with current settings
//     updateUIWithSettings(data.features);
// });
// 
// // Toggle a feature
// document.getElementById('feature_nav_home').addEventListener('change', async (e) => {
//     const feature = 'nav_home';
//     const enabled = e.target.checked;
//     
//     try {
//         const result = await settingsManager.toggleFeature(feature, enabled);
//         showSuccessMessage(result.message);
//     } catch (error) {
//         showErrorMessage('Failed to update feature');
//         e.target.checked = !enabled; // Revert checkbox
//     }
// });
