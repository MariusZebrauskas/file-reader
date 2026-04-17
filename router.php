<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$path = __DIR__ . $uri;
if ($uri !== '/' && is_file($path)) {
    return false;
}
header('Content-Type: text/html; charset=utf-8');
require __DIR__ . '/index.php';
