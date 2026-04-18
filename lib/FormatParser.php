<?php

declare(strict_types=1);

namespace App\Lib;

/**
 * Base parser: one string from config (e.g. "csv"); extensions become [that string].
 */
abstract class FormatParser
{
    private readonly string $name;

    /** @var list<string> */
    private readonly array $extensions;

    /**
     * @param string $name Format id and sole file extension (e.g. "csv")
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->extensions = [$name];
    }

    /**
     * Stable format identifier (same as configured name / extension).
     *
     * @return string
     */
    public function id(): string
    {
        return $this->name;
    }

    /**
     * File extensions this parser accepts (one entry, same as id).
     *
     * @return list<string>
     */
    public function extensions(): array
    {
        return $this->extensions;
    }

    /**
     * @return array{ok: true, columns: list<string>, rows: list<list<string>>}|array{ok: false, errors: list<string>}
     */
    abstract public function parse(string $path): array;
}
