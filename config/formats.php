<?php

declare(strict_types=1);

use App\Lib\CsvParser;
use App\Lib\JsonParser;
use App\Lib\XmlRowsParser;

/**
 * Registered parser classes.
 * Add a new parser class here for extensibility.
 *
 * @return list<class-string>
 */
return [
    CsvParser::class,
    JsonParser::class,
    XmlRowsParser::class,
];
