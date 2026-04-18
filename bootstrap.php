<?php

declare(strict_types=1);

/**
 * Project autoloader (PSR-4 without Composer).
 */
// PHP calls this when a class is used but not loaded yet; $class is the FQCN (e.g. App\Lib\CsvParser).
spl_autoload_register(
    static function (string $class): void
    {
        $prefix = 'App\\';
        // Only handle our App\ namespace; let other autoloaders (or PHP) deal with the rest.
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        // Drop "App\" and turn namespace separators into path segments: Lib\CsvParser -> Lib/CsvParser
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $parts = explode('/', $relative);
        // First segment matches disk folder case (lib/ not Lib/) for this project.
        if (isset($parts[0])) {
            $parts[0] = strtolower($parts[0]);
        }
        // Project root + path from namespace + .php (e.g. .../lib/CsvParser.php).
        $path = __DIR__ . '/' . implode('/', $parts) . '.php';
        // Avoid fatals if the class name does not match a real file.
        if (is_file($path)) {
            // Load the class definition; _once avoids loading the same file twice in one request.
            require_once $path;
        }
    }
);
