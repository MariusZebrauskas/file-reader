<?php

declare(strict_types=1);

// Path only (no ?query); built-in server passes every request through this script first.
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$path = __DIR__ . $uri;
if ($uri !== '/' && is_file($path)) {
    // Tell PHP's dev server to serve that file as a static asset (e.g. /public/css/global.css).
    return false;
}
header('Content-Type: text/html; charset=utf-8');
// Everything else runs the app (upload UI + parse flow).
require __DIR__ . '/src/routes/home/index.php';
