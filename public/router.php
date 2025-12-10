<?php
/**
 * Router for PHP Built-in Server
 * Handles URL rewriting to support API endpoints and clean URLs
 */

$requestUri = $_SERVER["REQUEST_URI"];
$uri = parse_url($requestUri, PHP_URL_PATH);
$docRoot = __DIR__;

// Serve static files and directories directly (but not .php files)
if ($uri !== '/' && file_exists("$docRoot$uri") && !preg_match('/\.php$/', $uri)) {
    if (is_dir("$docRoot$uri")) {
        // Try to serve index.php from the directory
        if (file_exists("$docRoot$uri/index.php")) {
            $_SERVER["SCRIPT_FILENAME"] = "$docRoot$uri/index.php";
        }
    } else {
        return false; // Serve the static file
    }
}

// Handle API routes
if (preg_match('#^/api/auth/#', $uri)) {
    $action = str_replace('/api/auth/', '', $uri);
    $_GET['action'] = $action ?: 'login';
    include "$docRoot/api/auth.php";
    return;
}

if (preg_match('#^/api/usulan/#', $uri)) {
    $action = str_replace('/api/usulan/', '', $uri);
    $_GET['action'] = $action ?: 'index';
    include "$docRoot/api/usulan.php";
    return;
}

// Default to index.php for all other routes
$_SERVER["SCRIPT_FILENAME"] = "$docRoot/index.php";
include "$docRoot/index.php";
return;
?>
