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

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->extensions = [$name];
    }

    public function id(): string
    {
        return $this->name;
    }

    /** @return list<string> */
    public function extensions(): array
    {
        return $this->extensions;
    }

    /**
     * @return array{ok: true, columns: list<string>, rows: list<list<string>>}|array{ok: false, errors: list<string>}
     */
    abstract public function parse(string $path): array;
}
