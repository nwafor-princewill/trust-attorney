<?php
/**
 * Decentralized Trust Attorneys - Site Configuration
 *
 * On Railway: don't edit the DB_* values below. Instead, add a MySQL
 * service to your Railway project — Railway auto-injects MYSQLHOST,
 * MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT as environment
 * variables into this app automatically, and the getenv() calls below
 * pick them up with zero editing.
 *
 * Running locally / on cPanel instead? Just set real values in the
 * fallback (second) argument of each getenv() call below, or export
 * the same environment variable names before starting PHP.
 */

// ---- Database settings (Railway env vars, with local fallbacks) ----
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
define('DB_NAME', getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: 'change_me_dbname'));
define('DB_USER', getenv('MYSQLUSER') ?: 'change_me_dbuser');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: 'change_me_dbpass');

// ---- Site settings ----
define('SITE_NAME', getenv('SITE_NAME') ?: 'Decentralized Trust Attorneys');
define('SITE_URL', getenv('SITE_URL') ?: 'https://decenttrustattorneys.com'); // set the SITE_URL env var to your real domain
define('SUPPORT_EMAIL', getenv('SUPPORT_EMAIL') ?: 'contact@decenttrustattorneys.com');
define('SUPPORT_PHONE', getenv('SUPPORT_PHONE') ?: '(307) 555-0123');

// ---- Session ----
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');
error_reporting(E_ALL);
// Keep this off in production. Set the DISPLAY_ERRORS env var to '1' temporarily if you need to debug on Railway.
ini_set('display_errors', getenv('DISPLAY_ERRORS') === '1' ? '1' : '0');
