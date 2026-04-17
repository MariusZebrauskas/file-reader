<?php

declare(strict_types=1);

/**
 * Project autoloader (PSR-4 without Composer).
 */
spl_autoload_register(
    static function (string $class): void
    {
        $prefix = 'App\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $parts = explode('/', $relative);
        if (isset($parts[0])) {
            $parts[0] = strtolower($parts[0]);
        }
        $path = __DIR__ . '/' . implode('/', $parts) . '.php';
        if (is_file($path)) {
            require_once $path;
        }
    }
);
