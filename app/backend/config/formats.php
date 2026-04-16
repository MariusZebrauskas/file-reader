<?php

declare(strict_types=1);

/**
 * New format: add a class (implements FormatParser), require it here, and append to the array.
 * You do not need to change parse.php or ParserRegistry.
 */
require_once dirname(__DIR__) . '/lib/FormatParser.php';
require_once dirname(__DIR__) . '/lib/CsvParser.php';
require_once dirname(__DIR__) . '/lib/JsonParser.php';
require_once dirname(__DIR__) . '/lib/XmlRowsParser.php';

return [
  new CsvParser(),
  new JsonParser(),
  new XmlRowsParser(),
];
