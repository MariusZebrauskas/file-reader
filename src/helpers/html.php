<?php

declare(strict_types=1);

/**
 * Escapes text for HTML output.
 */
function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
