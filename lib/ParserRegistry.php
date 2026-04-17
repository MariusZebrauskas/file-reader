<?php

declare(strict_types=1);

namespace App\Lib;

/**
 * Parser discovery and resolving by file extension.
 */
final class ParserRegistry
{
    /** @return list<FormatParser> */
    public static function parsers(): array
    {
        $classes = require dirname(__DIR__) . '/config/formats.php';
        $parsers = [];
        foreach ($classes as $class) {
            $parsers[] = new $class();
        }

        return $parsers;
    }

    /** @param list<FormatParser> $parsers */
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

    /** @param list<FormatParser> $parsers */
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
