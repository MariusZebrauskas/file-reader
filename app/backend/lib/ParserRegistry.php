<?php

declare(strict_types=1);

final class ParserRegistry
{
  /** @return list<FormatParser> */
  public static function parsers(): array
  {
    return require dirname(__DIR__) . '/config/formats.php';
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
