<?php

declare(strict_types=1);

namespace App\Controller;

use App\Lib\FormatParser;
use App\Lib\ParserRegistry;
use App\Model\HomeUploadModel;

/**
 * Home route: wires session flash (PRG), model, and view.
 */
final class HomeController
{
    public static function index(): void
    {
        require_once dirname(__DIR__) . '/src/routes/home/helpers/upload_flash.php';
        require_once dirname(__DIR__) . '/src/helpers/html.php';

        $parsers = ParserRegistry::parsers();
        $extensions = ParserRegistry::allowedExtensions($parsers);
        $maxBytes = FormatParser::$maxBytes;
        $maxMb = (string) (int) ($maxBytes / (1024 * 1024)) . ' MB';

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $state = HomeUploadModel::processPostUpload(
                isset($_FILES['file']) && is_array($_FILES['file']) ? $_FILES['file'] : null,
                $parsers,
                $maxBytes,
                $maxMb,
                $extensions,
            );
            uploadFlash($state['errors'], $state['columns'], $state['rows'], $state['fileName'], $state['format']);
        }

        [$errors, $columns, $rows, $fileName, $format] = uploadFlash();

        require dirname(__DIR__) . '/src/routes/home/view.php';
    }
}
