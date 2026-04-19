<?php

declare(strict_types=1);

/**
 * Path part of the current request (no query), for same-page POST/redirect.
 */
function request_path(): string
{
    $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (!is_string($p) || $p === '') {
        return '/';
    }
    return $p;
}

/**
 * Post/Redirect/Get: send 303 after POST so refresh does not re-submit the form.
 *
 * @param string|null $location Absolute path or full URL; null/'' = current path
 */
function redirect_after_post(?string $location = null): never
{
    if ($location === null || $location === '') {
        $location = request_path();
    }
    header('Location: ' . $location, true, 303);
    exit;
}
