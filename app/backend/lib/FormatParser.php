<?php

declare(strict_types=1);

interface FormatParser
{
  public function id(): string;

  /** @return list<string> */
  public function extensions(): array;

  /**
   * @return array{ok: true, columns: list<string>, rows: list<list<string>>}|array{ok: false, errors: list<string>}
   */
  public function parse(string $path): array;
}
