<?php
/**
 * Anonymous install/version check-in endpoint.
 *
 * Set this to the HTTPS URL of your receiver before release, for example:
 * https://your-domain.example/install-checkin/collect.php
 */
const DCS_STATS_INSTALL_CHECKIN_ENDPOINT = 'https://dcs-statistics-dashboard-checkin.dcsstatisticsdashboard.app/collect.php';

/**
 * Optional shared receiver token.
 *
 * If your receiver is configured with a token, put the same value here.
 * Do not treat this as a private secret if it is committed to a public repo.
 */
const DCS_STATS_INSTALL_CHECKIN_TOKEN = 'DCS_STATS_DASHBOARD_V1_2_CHECKIN';

/**
 * Allows a retry without local certificate verification if the local PHP
 * certificate bundle is outdated. The check-in contains no personal data.
 */
const DCS_STATS_INSTALL_CHECKIN_ALLOW_SSL_FALLBACK = true;
