<?php

declare(strict_types=1);

use App\Lib\CsvParser;
use App\Lib\FormatParser;
use App\Lib\JsonParser;
use App\Lib\XmlRowsParser;

/**
 * Each row: [parser class, format name] — extensions are [name] inside {@see FormatParser}.
 *
 * @return list<array{0: class-string<FormatParser>, 1: string}>
 */
return [
    [CsvParser::class, 'csv'],
    [JsonParser::class, 'json'],
    [XmlRowsParser::class, 'xml'],
];
