<?php

declare(strict_types=1);

namespace App\Lib;

/**
 * Parser discovery and resolving by file extension.
 */
final class ParserRegistry
{
    /**
     * Instantiates all parsers declared in config/formats.php (project root).
     *
     * @return list<FormatParser>
     */
    public static function parsers(): array
    {
        $rows = require dirname(__DIR__) . '/config/formats.php';
        $parsers = [];
        foreach ($rows as $row) {
            $class = $row[0];
            $parsers[] = new $class($row[1]);
        }

        return $parsers;
    }

    /**
     * Picks the parser whose extension list matches the file's extension (case-insensitive).
     *
     * @param string $fileName Original client file name (basename used for extension)
     * @param list<FormatParser> $parsers Parsers from {@see self::parsers()}
     * @return FormatParser|null Matching parser or null if extension unsupported
     */
    public static function resolve(string $fileName, array $parsers): ?FormatParser
    {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        foreach ($parsers as $p) {
            if (in_array($ext, $p->extensions(), true)) {
                return $p;
            }
        }

        return null;
    }

    /**
     * Union of all extensions accepted by the given parsers (unique, order not guaranteed).
     *
     * @param list<FormatParser> $parsers Parsers from {@see self::parsers()}
     * @return list<string>
     */
    public static function allowedExtensions(array $parsers): array
    {
        $all = [];
        foreach ($parsers as $p) {
            foreach ($p->extensions() as $e) {
                $all[] = $e;
            }
        }

        return array_values(array_unique($all));
    }
}
