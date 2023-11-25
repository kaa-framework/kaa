<?php

declare(strict_types=1);

namespace Kaa\Component\HttpMessage;

use InvalidArgumentException;

class HeaderUtils
{
    public const DISPOSITION_ATTACHMENT = 'attachment';
    public const DISPOSITION_INLINE = 'inline';

    /**
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Splits an HTTP header by one or more separators.
     *
     * Example:
     *
     *     HeaderUtils::split("da, en-gb;q=0.8", ",;")
     *     // => ['da'], ['en-gb', 'q=0.8']]
     *
     * @param string $separators List of characters to split on, ordered by
     *                           precedence, e.g. ",", ";=", or ",;="
     *
     * @return mixed Nested array with as many levels as there are characters in
     *               $separators
     */
    public static function split(string $header, string $separators)
    {
        $quotedSeparators = preg_quote($separators, '/');

        $pattern = '/
                (?!\s)
                    (?:
                        # quoted-string
                        "(?:[^"\\\\]|\\\\.)*(?:"|\\\\|$)
                    |
                        # token
                        [^"' . $quotedSeparators . ']+
                    )+
                (?<!\s)
            |
                # separator
                \s*
                (?<separator>[' . $quotedSeparators . '])
                \s*
            /x';

        preg_match_all($pattern, trim($header), $matches, \PREG_SET_ORDER);

        return self::groupParts($matches, $separators);
    }

    /**
     * Combines an array of arrays into one associative array.
     *
     * Each of the nested arrays should have one or two elements. The first
     * value will be used as the keys in the associative array, and the second
     * will be used as the values, or true if the nested array only contains one
     * element. Array keys are lowercased.
     *
     * Example:
     *
     *     HeaderUtils::combine([["foo", "abc"], ["bar"]])
     *     // => ["foo" => "abc", "bar" => true]
     *
     * @param mixed $parts
     * @return string[]|bool[]
     */
    public static function combine($parts)
    {
        /** @var string[]|bool[] $assoc */
        $assoc = [];
        foreach ($parts as $part) {
            $name = strtolower($part[0]);
            $value = $part[1] ?? true;
            $assoc[$name] = $value;
        }

        return $assoc;
    }

    /**
     * Joins an associative array into a string for use in an HTTP header.
     *
     * The key and value of each entry are joined with "=", and all entries
     * are joined with the specified separator and an additional space (for
     * readability). Values are quoted if necessary.
     *
     * Example:
     *
     *     HeaderUtils::toString(["foo" => "abc", "bar" => "true", "baz" => "a b c"], ",")
     *     // => 'foo=abc, bar, baz="a b c"'
     * @param mixed $assoc
     */
    public static function toString($assoc, string $separator): string
    {
        $parts = [];
        foreach ($assoc as $name => $value) {
            // Checking for type is necessary, otherwise the KPHP compiler complains
            if (is_bool($value) && $value === true) {
                $parts[] = $name;
            } else {
                $parts[] = $name . '=' . self::quote((string) $value);
            }
        }

        return implode($separator . ' ', $parts);
    }

    /**
     * Encodes a string as a quoted string, if necessary.
     *
     * If a string contains characters not allowed by the "token" construct in
     * the HTTP specification, it is backslash-escaped and enclosed in quotes
     * to match the "quoted-string" construct.
     */
    public static function quote(string $s): string
    {
        if ((bool) preg_match('/^[a-z0-9!#$%&\'*.^_`|~-]+$/i', $s, $matches)) {
            return $s;
        }

        return '"' . addcslashes($s, '"\\"') . '"';
    }

    /**
     * Decodes a quoted string.
     *
     * If passed an unquoted string that matches the "token" construct (as
     * defined in the HTTP specification), it is passed through verbatim.
     *
     * @return mixed
     */
    public static function unquote(string $s)
    {
        return preg_replace('/\\\\(.)|"/', '$1', $s);
    }

    /**
     * Generates an HTTP Content-Disposition field-value.
     *
     * @param string $disposition      One of "inline" or "attachment"
     * @param string $filename         A unicode string
     * @param string $filenameFallback A string containing only ASCII characters that
     *                                 is semantically equivalent to $filename. If the filename is already ASCII,
     *                                 it can be omitted, or just copied from $filename
     *
     * @throws InvalidArgumentException
     *
     * @see RFC 6266
     */
    public static function makeDisposition(string $disposition, string $filename, string $filenameFallback = ''): string
    {
        if (!in_array($disposition, [self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE], true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The disposition must be either "%s" or "%s".',
                    self::DISPOSITION_ATTACHMENT,
                    self::DISPOSITION_INLINE,
                ),
            );
        }

        if ($filenameFallback === '') {
            $filenameFallback = $filename;
        }

        // filenameFallback is not ASCII.
        if (!(bool) preg_match('/^[\x20-\x7e]*$/', $filenameFallback, $matches)) {
            throw new InvalidArgumentException('The filename fallback must only contain ASCII characters.');
        }

        // percent characters aren't safe in fallback.
        if (strpos($filenameFallback, '%') !== false) {
            throw new InvalidArgumentException('The filename fallback cannot contain the "%" character.');
        }

        // path separators aren't allowed in either.
        if (
            (strpos($filename, '/') !== false) || (strpos($filename, '\\') !== false) ||
            (strpos($filenameFallback, '/') !== false) || (strpos($filenameFallback, '\\') !== false)
        ) {
            throw new InvalidArgumentException(
                'The filename and the fallback cannot contain the "/" and "\\" characters.',
            );
        }

        $params = ['filename' => $filenameFallback];
        if ($filename !== $filenameFallback) {
            $params['filename*'] = "utf-8''" . rawurlencode($filename);
        }

        return $disposition . '; ' . self::toString($params, ';');
    }

    /**
     * Like parse_str(), but preserves dots in variable names.
     * @return mixed
     */
    public static function parseQuery(string $query, bool $ignoreBrackets = false, string $separator = '&')
    {
        /** @var string[][]|string[] $q */
        $q = [];

        if ($separator === '') {
            $separator = '&';
        }

        foreach (explode($separator, $query) as $v) {
            $i = strpos($v, "\0");
            if ($i !== false) {
                $v = substr($v, 0, $i);
            }

            $i = strpos($v, '=');
            if ($i === false) {
                $k = urldecode($v);
                $v = '';
            } else {
                $k = urldecode(substr($v, 0, $i));
                $v = substr($v, $i);
            }

            $i = strpos($k, "\0");
            if ($i !== false) {
                $k = substr($k, 0, $i);
            }

            $k = ltrim($k, ' ');

            if ($ignoreBrackets) {
                /**
                 * @phpstan-ignore-next-line
                 */
                $q[$k][] = urldecode(substr($v, 1));

                continue;
            }

            $i = strpos($k, '[');
            if ($i === false) {
                $q[] = bin2hex($k) . $v;
            } else {
                $q[] = bin2hex(substr($k, 0, $i)) . rawurlencode(substr($k, $i)) . $v;
            }
        }

        if ($ignoreBrackets) {
            return $q;
        }

        /** @phpstan-ignore-next-line */
        parse_str(implode('&', $q), $qArray);

        $query = [];

        foreach ($qArray as $k => $v) {
            $k = (string) $k;

            if ($k === '') {
                continue;
            }

            $i = strpos($k, '_');

            if ($i !== false) {
                $query[substr_replace($k, hex2bin(substr($k, 0, $i)) . '[', 0, 1 + $i)] = $v;
            } else {
                $query[hex2bin($k)] = $v;
            }
        }

        return $query;
    }

    /**
     * @param mixed $matches
     * @return mixed
     */
    private static function groupParts($matches, string $separators, bool $first = true)
    {
        $separator = $separators[0];
        $partSeparators = substr($separators, 1);

        $i = 0;
        $partMatches = [];
        $previousMatchWasSeparator = false;
        foreach ($matches as $match) {
            if (
                !$first && $previousMatchWasSeparator &&
                array_key_exists('separator', $match) && ($match['separator'] === $separator)
            ) {
                $previousMatchWasSeparator = true;
                $partMatches[$i][] = $match;
            } elseif (array_key_exists('separator', $match) && $match['separator'] === $separator) {
                $previousMatchWasSeparator = true;
                $i++;
            } else {
                $previousMatchWasSeparator = false;
                $partMatches[$i][] = $match;
            }
        }

        $parts = [];
        /**
         * Тута php-stan жалуется на перезапись итерируемого массива $matches,
         * если ты знаешь как это переписать, то вперёд ))
         */
        if ((bool) $partSeparators) {
            /** @phpstan-ignore-next-line */
            foreach ($partMatches as $matches) {
                $parts[] = self::groupParts($matches, $partSeparators, false);
            }
        } else {
            /** @phpstan-ignore-next-line */
            foreach ($partMatches as $matches) {
                $parts[] = self::unquote((string) $matches[0][0]);
            }

            if (!$first && count($parts) > 2) {
                $parts = [
                    $parts[0],
                    implode($separator, array_slice($parts, 1)),
                ];
            }
        }

        return $parts;
    }
}
