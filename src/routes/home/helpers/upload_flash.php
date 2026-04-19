<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/helpers/http.php';

/** @var non-empty-string */
const UPLOAD_FLASH_KEY = '_fr_upload_flash';

/**
 * PRG: no args — session + restore flash on GET (else empty state).
 * Five args — flash state then 303 redirect (exits).
 *
 * @return array{0: array<int|string, mixed>, 1: array<int|string, mixed>, 2: array<int|string, mixed>, 3: string, 4: string}
 */
function uploadFlash(mixed ...$args): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (count($args) === 5) {
        $_SESSION[UPLOAD_FLASH_KEY] = [
            'errors' => $args[0],
            'columns' => $args[1],
            'rows' => $args[2],
            'fileName' => $args[3],
            'format' => $args[4],
        ];
        redirect_after_post();
    }
    $errors = [];
    $columns = [];
    $rows = [];
    $fileName = '';
    $format = '';
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET' || !isset($_SESSION[UPLOAD_FLASH_KEY]) || !is_array($_SESSION[UPLOAD_FLASH_KEY])) {
        return [$errors, $columns, $rows, $fileName, $format];
    }
    $f = $_SESSION[UPLOAD_FLASH_KEY];
    unset($_SESSION[UPLOAD_FLASH_KEY]);
    if (isset($f['errors']) && is_array($f['errors'])) {
        $errors = $f['errors'];
    }
    if (isset($f['columns']) && is_array($f['columns'])) {
        $columns = $f['columns'];
    }
    if (isset($f['rows']) && is_array($f['rows'])) {
        $rows = $f['rows'];
    }
    if (isset($f['fileName']) && is_string($f['fileName'])) {
        $fileName = $f['fileName'];
    }
    if (isset($f['format']) && is_string($f['format'])) {
        $format = $f['format'];
    }
    return [$errors, $columns, $rows, $fileName, $format];
}
