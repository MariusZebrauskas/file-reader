<?php

declare(strict_types=1);

/**
 * Home route: upload UI, POST handling, table preview.
 */
require_once dirname(__DIR__, 3) . '/bootstrap.php';

use App\Lib\FormatParser;
use App\Lib\ParserRegistry;

/**
 * @param string $value Raw UTF-8 string
 */
function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$errors = [];
$columns = [];
$rows = [];
$fileName = '';
$format = '';
$maxBytes = FormatParser::$maxBytes;
$maxMb = (string) (int) ($maxBytes / (1024 * 1024)) . ' MB';
$parsers = ParserRegistry::parsers();
$extensions = ParserRegistry::allowedExtensions($parsers);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
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
                $errors[] = 'The file is too big. Maximum allowed size is ' . $maxMb . '.';
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
    <link rel="stylesheet" href="/public/css/global.css">
    <link rel="stylesheet" href="/src/routes/home/home.css">
</head>
<body>
<main>
    <header>
        <h1>Program - Read from file</h1>
        <p>Supported formats: .<?= h(implode(', .', $extensions)) ?></p>
    </header>
    <form id="upload-form" method="post" enctype="multipart/form-data">
        <div id="upload-pick">
            <span class="sr-only">Choose file. Formats: <?= h(implode(', ', array_map('strtoupper', $extensions))) ?>. Maximum size <?= h($maxMb) ?>.</span>
            <span>Select a file (<?= h(implode(', ', array_map('strtoupper', $extensions))) ?>)</span>
            <div>
                <span aria-hidden="true"><i></i></span>
                <span id="file-name-display"><?= $fileName !== '' ? h($fileName) : 'No file chosen' ?></span>
            </div>
            <p>Maximum file size: <?= h($maxMb) ?>.</p>
            <input id="file" name="file" type="file" class="sr-only" required accept="<?= h(implode(',', array_map(static fn (string $e): string => '.' . $e, $extensions))) ?>">
        </div>
        <aside id="upload-drop">
            <i aria-hidden="true"></i>
            <span>Drop file here</span>
        </aside>
    </form>
    <?php if (count($errors) > 0) { ?>
        <div id="errors" role="alert">
            <strong>Errors:</strong>
            <ul>
                <?php foreach ($errors as $error) { ?>
                    <li><?= h($error) ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>
    <?php if (count($rows) > 0) { ?>
        <section id="preview">
            <p><strong>File:</strong> <?= h($fileName) ?> | <strong>Format:</strong> <?= h(strtoupper($format)) ?></p>
            <div>
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
    function byId(id) {
        return document.getElementById(id);
    }
    const form = byId('upload-form');
    const input = byId('file');
    const zone = byId('upload-pick');
    const drop = byId('upload-drop');
    const display = byId('file-name-display');
    if (!form || !input || !zone || !drop || !display) {
        return;
    }
    zone.addEventListener('click', function () {
        input.click();
    });
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function (ev) {
        zone.addEventListener(ev, function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (ev === 'dragover') {
                e.dataTransfer.dropEffect = 'none';
            }
        });
    });
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

    drop.addEventListener('dragenter', function (e) {
        e.preventDefault();
        drop.classList.add('is-drag-over');
    });
    drop.addEventListener('dragleave', function (e) {
        e.preventDefault();
        drop.classList.remove('is-drag-over');
    
    });
    drop.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
    });
    drop.addEventListener('drop', function (e) {
        e.preventDefault();
        drop.classList.remove('is-drag-over');
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
