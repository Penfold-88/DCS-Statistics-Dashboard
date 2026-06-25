function autofillIP() {
    document.getElementById('ip_address').value = window.DCS_ADMIN_MAINTENANCE_CONFIG?.currentIP || '';
}
