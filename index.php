<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Lib\ParserRegistry;

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$errors = [];
$columns = [];
$rows = [];
$fileName = '';
$format = '';
$maxBytes = 2 * 1024 * 1024;
$parsers = ParserRegistry::parsers();
$extensions = ParserRegistry::allowedExtensions($parsers);
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
                $errors[] = 'File is too large (max 2 MB).';
            }
            if (count($errors) === 0) {
                $fileName = basename((string) $file['name']);
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
    <form method="post" enctype="multipart/form-data">
        <div class="file-input-area">

            <label for="file">Select a file (CSV, XML, JSON)</label>
            <input id="file" name="file" type="file" required accept=".csv,.xml,.json">
            <button type="submit">Read</button>
        </div>
        <div class="drop-area">
            <label for="file">Drop area</label>
            <input class="file-input" id="file" name="file" type="file" required accept=".csv,.xml,.json">
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
</body>
</html>
