<?php

declare(strict_types=1);

namespace App\Model;

use App\Lib\ParserRegistry;

/**
 * Upload + parse flow for the home page (no session; caller handles flash/redirect).
 */
final class HomeUploadModel
{
    /**
     * Validates $_FILES row and parses supported formats.
     *
     * @param array<string, mixed>|null $file One row from $_FILES['file']
     * @param list<\App\Lib\FormatParser> $parsers
     * @param list<string> $extensions
     * @return array{errors: list<string>, columns: list<string>, rows: list<list<string>>, fileName: string, format: string}
     */
    public static function processPostUpload(?array $file, array $parsers, int $maxBytes, string $maxMb, array $extensions): array
    {
        $errors = [];
        $columns = [];
        $rows = [];
        $fileName = '';
        $format = '';
        if ($file === null) {
            $errors[] = 'No file was uploaded.';
            return ['errors' => $errors, 'columns' => $columns, 'rows' => $rows, 'fileName' => $fileName, 'format' => $format];
        }
        if (!is_array($file) || !isset($file['error'], $file['name'], $file['tmp_name'], $file['size'])) {
            $errors[] = 'Invalid uploaded file data.';
            return ['errors' => $errors, 'columns' => $columns, 'rows' => $rows, 'fileName' => $fileName, 'format' => $format];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed.';
        }
        if ((int) $file['size'] > $maxBytes) {
            $errors[] = 'The file is too big. Maximum allowed size is ' . $maxMb . '.';
        }
        if (count($errors) > 0) {
            return ['errors' => $errors, 'columns' => $columns, 'rows' => $rows, 'fileName' => $fileName, 'format' => $format];
        }
        $fileName = basename((string) $file['name']);
        $parser = ParserRegistry::resolve($fileName, $parsers);
        if ($parser === null) {
            $errors[] = 'Unsupported format. Allowed: .' . implode(', .', $extensions);
            return ['errors' => $errors, 'columns' => $columns, 'rows' => $rows, 'fileName' => $fileName, 'format' => $format];
        }
        $result = $parser->parse((string) $file['tmp_name']);
        if ($result['ok'] === false) {
            return ['errors' => array_merge($errors, $result['errors']), 'columns' => $columns, 'rows' => $rows, 'fileName' => $fileName, 'format' => $format];
        }

        return [
            'errors' => $errors,
            'columns' => $result['columns'],
            'rows' => $result['rows'],
            'fileName' => $fileName,
            'format' => $parser->id(),
        ];
    }
}
