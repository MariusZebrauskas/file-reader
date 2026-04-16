<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
if (str_starts_with($uri, '/api/')) {
  return false;
}
$path = __DIR__ . $uri;
if ($uri !== '/' && is_file($path)) {
  return false;
}
header('Content-Type: text/html; charset=utf-8');
readfile(__DIR__ . '/index.html');
