<?php

declare(strict_types=1);

namespace App\Lib;

use JsonException;

/**
 * JSON parser implementation.
 */
final class JsonParser extends FormatParser
{
    public function parse(string $path): array
    {
        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return ['ok' => false, 'errors' => ['Empty file.']];
        }
        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return ['ok' => false, 'errors' => ['JSON: invalid syntax - ' . $e->getMessage() . '.']];
        }
        if (!is_array($data)) {
            return ['ok' => false, 'errors' => ['JSON: expected an array of objects.']];
        }
        if (count($data) === 0) {
            return ['ok' => false, 'errors' => ['JSON: array is empty.']];
        }
        $first = $data[0];
        if (!is_array($first) || array_is_list($first)) {
            return ['ok' => false, 'errors' => ['JSON: each item must be an object (keys -> values).']];
        }
        $columns = array_keys($first);
        $rows = [];
        foreach ($data as $idx => $row) {
            if (!is_array($row) || array_is_list($row)) {
                return ['ok' => false, 'errors' => ['JSON: row ' . ($idx + 1) . ' is not an object.']];
            }
            $line = [];
            foreach ($columns as $col) {
                if (!array_key_exists($col, $row)) {
                    return ['ok' => false, 'errors' => ['JSON: missing field "' . $col . '" in row ' . ($idx + 1) . '.']];
                }
                $line[] = self::cell($row[$col]);
            }
            $extra = array_diff(array_keys($row), $columns);
            if (count($extra) > 0) {
                return ['ok' => false, 'errors' => ['JSON: unknown fields in row ' . ($idx + 1) . ': ' . implode(', ', $extra) . '.']];
            }
            $rows[] = $line;
        }

        return ['ok' => true, 'columns' => $columns, 'rows' => $rows];
    }

    private static function cell(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if (is_bool($v)) {
            return $v ? '1' : '0';
        }
        if (is_int($v) || is_float($v)) {
            return (string) $v;
        }
        if (is_string($v)) {
            return $v;
        }

        return json_encode($v, JSON_UNESCAPED_UNICODE) ?: '';
    }
}
