<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'errors' => ['Only POST is allowed.']], JSON_UNESCAPED_UNICODE);
  exit;
}

$maxBytes = 2 * 1024 * 1024;
if (!isset($_FILES['file'])) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'errors' => ['No file was uploaded.']], JSON_UNESCAPED_UNICODE);
  exit;
}

$f = $_FILES['file'];
if ($f['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'errors' => ['Upload failed.']], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($f['size'] > $maxBytes) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'errors' => ['File is larger than 2 MB.']], JSON_UNESCAPED_UNICODE);
  exit;
}

require_once dirname(__DIR__, 2) . '/lib/ParserRegistry.php';

$name = basename($f['name']);
$tmp = $f['tmp_name'];
$parsers = ParserRegistry::parsers();
$parser = ParserRegistry::resolve($name, $parsers);

if ($parser === null) {
  http_response_code(400);
  echo json_encode(
    [
      'ok' => false,
      'errors' => [
        'Unsupported extension. Allowed: .' . implode(', .', ParserRegistry::allowedExtensions($parsers)),
      ],
    ],
    JSON_UNESCAPED_UNICODE
  );
  exit;
}

$result = $parser->parse($tmp);
if ($result['ok'] === false) {
  http_response_code(400);
  echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
  exit;
}

echo json_encode(
  [
    'ok' => true,
    'format' => $parser->id(),
    'fileName' => $name,
    'columns' => $result['columns'],
    'rows' => $result['rows'],
  ],
  JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
);
