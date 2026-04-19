<?php

declare(strict_types=1);

/**
 * Front script: registers autoloading, handles file upload POST, renders HTML table or errors.
 */
require_once __DIR__ . '/bootstrap.php';

use App\Lib\ParserRegistry;

/**
 * Escapes text for safe insertion into HTML (mitigates XSS).
 *
 * @param string $value Raw UTF-8 string
 * @return string HTML-escaped string
 */
function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/*
 * Page state (GET default; POST fills from parser on success, or error strings on failure).
 */
$errors = [];
$columns = [];
$rows = [];
$fileName = '';
$format = '';
$maxBytes = 2 * 1024 * 1024; // 2 MiB (~2 MB)
$maxSizeLabel = (string) (int) ($maxBytes / (1024 * 1024)) . ' MB';
$parsers = ParserRegistry::parsers();
$extensions = ParserRegistry::allowedExtensions($parsers);

/*
 * POST: validate $_FILES (presence, PHP upload error, size), extension vs registry, then parse temp path.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file'])) {
        $errors[] = 'No file was uploaded.';
    } else {
        $file = $_FILES['file'];
        if (!is_array($file) || !isset($file['error'], $file['name'], $file['tmp_name'], $file['size'])) {
            $errors[] = 'Invalid uploaded file data.';
        } else {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'File upload failed.';
            }
            if ((int) $file['size'] > $maxBytes) {
                $errors[] = 'File is too large (max ' . $maxSizeLabel . ').';
            }
            if (count($errors) === 0) {
                $fileName = basename((string) $file['name']);
                // Pick parser from extension; parse temp path PHP stored on disk.
                $parser = ParserRegistry::resolve($fileName, $parsers);
                if ($parser === null) {
                    $errors[] = 'Unsupported format. Allowed: .' . implode(', .', $extensions);
                } else {
                    $result = $parser->parse((string) $file['tmp_name']);
                    if ($result['ok'] === false) {
                        $errors = array_merge($errors, $result['errors']);
                    } else {
                        $format = $parser->id();
                        $columns = $result['columns'];
                        $rows = $result['rows'];
                    }
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read from file</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
<main class="container">
    <h1>Program - Read from file</h1>
    <p class="help">Supported formats: .<?= h(implode(', .', $extensions)) ?></p>
    <form id="upload-form" method="post" enctype="multipart/form-data">
        <label class="file-input-area">
            <span class="sr-only">Choose file. Formats: <?= h(implode(', ', array_map('strtoupper', $extensions))) ?>. Maximum size <?= h($maxSizeLabel) ?>.</span>
            <span class="file-hint">Select a file (<?= h(implode(', ', array_map('strtoupper', $extensions))) ?>)</span>
            <div class="file-picker">
                <span class="file-icon-wrap" aria-hidden="true">
                    <i class="icon icon-upload"></i>
                </span>
                <span id="file-name-display" class="file-name-display"><?= $fileName !== '' ? h($fileName) : 'No file chosen' ?></span>
            </div>
            <p class="file-max">Maximum file size: <?= h($maxSizeLabel) ?>.</p>
            <input id="file" name="file" type="file" class="file-input-overlay" required accept="<?= h(implode(',', array_map(static fn (string $e): string => '.' . $e, $extensions))) ?>">
        </label>
        <div class="drop-area">
            <i class="icon icon-drop" aria-hidden="true"></i>
            <span class="drop-area__text">Drop file here</span>
        </div>
    </form>
    <?php if (count($errors) > 0) { ?>
        <div class="errors">
            <strong>Errors:</strong>
            <ul>
                <?php foreach ($errors as $error) { ?>
                    <li><?= h($error) ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>
    <?php if (count($rows) > 0) { ?>
        <section class="result">
            <p><strong>File:</strong> <?= h($fileName) ?> | <strong>Format:</strong> <?= h(strtoupper($format)) ?></p>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <?php foreach ($columns as $column) { ?>
                            <th><?= h($column) ?></th>
                        <?php } ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row) { ?>
                        <tr>
                            <?php foreach ($row as $cell) { ?>
                                <td><?= h($cell) ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php } ?>
</main>
<script>
(function () {
    const form = document.getElementById('upload-form');
    const input = document.getElementById('file');
    const drop = document.querySelector('.drop-area');
    const display = document.getElementById('file-name-display');
    if (!form || !input || !drop || !display) {
        return;
    }
    const syncLabel = function () {
        display.textContent = input.files.length ? input.files[0].name : 'No file chosen';
    };
    let viaDrop = false;
    input.addEventListener('change', function () {
        syncLabel();
        if (!input.files.length || viaDrop) {
            return;
        }
        form.requestSubmit();
    });
    drop.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });
    drop.addEventListener('drop', function (e) {
        e.preventDefault();
        const list = e.dataTransfer.files;
        if (!list.length) {
            return;
        }
        const dt = new DataTransfer();
        dt.items.add(list[0]);
        viaDrop = true;
        input.files = dt.files;
        viaDrop = false;
        syncLabel();
        form.requestSubmit();
    });
})();
</script>
</body>
</html>
