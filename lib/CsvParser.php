<?php

declare(strict_types=1);

namespace App\Lib;

/**
 * CSV parser implementation.
 */
final class CsvParser implements FormatParser
{
    public function id(): string
    {
        return 'csv';
    }

    public function extensions(): array
    {
        return ['csv'];
    }

    public function parse(string $path): array
    {
        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return ['ok' => false, 'errors' => ['Empty file.']];
        }
        if (str_starts_with($raw, "\xef\xbb\xbf")) {
            $raw = substr($raw, 3);
        }
        $lines = preg_split("/\r\n|\n|\r/", $raw, -1, PREG_SPLIT_NO_EMPTY);
        if ($lines === false || count($lines) < 2) {
            return ['ok' => false, 'errors' => ['CSV: need a header row and at least one data row.']];
        }
        $headers = self::parseRow($lines[0]);
        if (count($headers) === 0) {
            return ['ok' => false, 'errors' => ['CSV: no columns found.']];
        }
        if (count($headers) !== count(array_unique($headers))) {
            return ['ok' => false, 'errors' => ['CSV: duplicate column names.']];
        }
        $rows = [];
        $n = count($headers);
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }
            $cells = self::parseRow($line);
            if (count($cells) !== $n) {
                return ['ok' => false, 'errors' => ['CSV: row ' . ($i + 1) . ' has ' . count($cells) . ' fields, expected ' . $n . '.']];
            }
            $rows[] = array_map(static fn (string $c): string => $c, $cells);
        }
        if (count($rows) === 0) {
            return ['ok' => false, 'errors' => ['CSV: no data rows.']];
        }

        return ['ok' => true, 'columns' => $headers, 'rows' => $rows];
    }

    /** @return list<string> */
    private static function parseRow(string $line): array
    {
        $fields = [];
        $cur = '';
        $inQuote = null;
        $len = strlen($line);
        for ($i = 0; $i < $len; $i++) {
            $c = $line[$i];
            if ($inQuote !== null) {
                if ($c === $inQuote) {
                    $inQuote = null;
                } else {
                    $cur .= $c;
                }
            } else {
                if ($c === "'" || $c === '"') {
                    $inQuote = $c;
                } elseif ($c === ',') {
                    $fields[] = $cur;
                    $cur = '';
                } else {
                    $cur .= $c;
                }
            }
        }
        $fields[] = $cur;

        return array_map('trim', $fields);
    }
}
