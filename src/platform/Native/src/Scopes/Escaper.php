<?php

namespace NativePlatform\Scopes;

class Escaper
{
    /**
     * Escapes a string for safe output in an HTML context.
     *
     * This method replaces special characters with HTML entities
     * to prevent Cross-Site Scripting (XSS) attacks. It also ensures
     * proper UTF-8 handling by substituting invalid characters with
     * the Unicode replacement character (�).
     *
     * Behavior:
     * - Converts both double and single quotes (ENT_QUOTES).
     * - Substitutes invalid UTF-8 sequences instead of silently ignoring them (ENT_SUBSTITUTE).
     * - Replaces tab characters with 4 spaces for consistent rendering.
     *
     * @param string $raw The raw, untrusted input string.
     * @return string The escaped, safe-to-output HTML string.
     */
    public static function rawEscaper(string $raw): string
    {
        $flags = ENT_QUOTES | ENT_SUBSTITUTE;

        // Normalize tabs to spaces (optional, cosmetic choice)
        $raw = str_replace("\t", '    ', $raw);

        return htmlspecialchars($raw, $flags, 'UTF-8');
    }
}
