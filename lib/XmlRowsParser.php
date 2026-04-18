<?php

declare(strict_types=1);

namespace App\Lib;

use DOMDocument;
use DOMElement;

/**
 * XML table parser: repeated child elements of root are rows; each row's child elements are columns.
 * (Class name avoids PHP's built-in XMLParser.)
 */
final class XmlRowsParser extends FormatParser
{
    /**
     * Reads XML from disk; root's homogeneous child tags are rows, sub-tags are column names.
     *
     * @param string $path Absolute path to readable XML file
     * @return array{ok: true, columns: list<string>, rows: list<list<string>>}|array{ok: false, errors: list<string>}
     */
    public function parse(string $path): array
    {
        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return ['ok' => false, 'errors' => ['Empty file.']];
        }
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        if (!$doc->loadXML($raw, LIBXML_NONET)) {
            $err = libxml_get_last_error();
            libxml_clear_errors();

            return ['ok' => false, 'errors' => ['XML: ' . ($err ? trim($err->message) : 'could not parse document.')]];
        }
        $root = $doc->documentElement;
        if ($root === null) {
            return ['ok' => false, 'errors' => ['XML: no root element.']];
        }
        $items = self::childElements($root);
        if (count($items) === 0) {
            return ['ok' => false, 'errors' => ['XML: root has no child elements.']];
        }
        $tag = $items[0]->tagName;
        foreach ($items as $el) {
            if ($el->tagName !== $tag) {
                return ['ok' => false, 'errors' => ['XML: every row under the root must be <' . $tag . '>.']];
            }
        }
        $firstCells = self::childElements($items[0]);
        if (count($firstCells) === 0) {
            return ['ok' => false, 'errors' => ['XML: row has no fields.']];
        }
        $columns = [];
        foreach ($firstCells as $c) {
            $columns[] = $c->tagName;
        }
        $rows = [];
        foreach ($items as $ri => $item) {
            $cells = self::childElements($item);
            if (count($cells) !== count($columns)) {
                return ['ok' => false, 'errors' => ['XML: row ' . ($ri + 1) . ' has the wrong number of fields.']];
            }
            $map = [];
            foreach ($cells as $c) {
                if (isset($map[$c->tagName])) {
                    return ['ok' => false, 'errors' => ['XML: duplicate field <' . $c->tagName . '>.']];
                }
                $map[$c->tagName] = trim($c->textContent);
            }
            $line = [];
            foreach ($columns as $col) {
                if (!array_key_exists($col, $map)) {
                    return ['ok' => false, 'errors' => ['XML: missing <' . $col . '> in row ' . ($ri + 1) . '.']];
                }
                $line[] = $map[$col];
            }
            $rows[] = $line;
        }

        return ['ok' => true, 'columns' => $columns, 'rows' => $rows];
    }

    /**
     * Direct child elements only (skips text/comment nodes).
     *
     * @param DOMElement $el Parent DOM node
     * @return list<DOMElement>
     */
    private static function childElements(DOMElement $el): array
    {
        $out = [];
        foreach ($el->childNodes as $n) {
            if ($n instanceof DOMElement) {
                $out[] = $n;
            }
        }

        return $out;
    }
}
