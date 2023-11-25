<?php

declare(strict_types=1);

namespace Kaa\Component\HttpMessage;

use DateTime;
use RuntimeException;

class HeaderBag
{
    protected const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const LOWER = '-abcdefghijklmnopqrstuvwxyz';

    /** @var string[][] */
    protected $headers = [];

    /** @var bool[]|string[] */
    protected $cacheControl = [];

    /**
     * @param string[][]|string[] $headers
     */
    public function __construct($headers = [])
    {
        foreach ($headers as $key => $values) {
            $this->set((string) $key, $values);
        }
    }

    /**
     * Returns the headers as a string.
     */
    public function __toString(): string
    {
        $headers = $this->all();
        if ($headers === []) {
            return '';
        }

        ksort($headers);
        $max = max(array_map(static function ($key) {
            if (is_string($key)) {
                return strlen($key);
            }

            return 0;
        }, array_keys($headers))) + 1;

        $content = '';
        foreach ($headers as $name => $values) {
            $name = strtolower($name);
            $delimiters = ['-'];
            foreach ($delimiters as $delimiter) {
                $words = explode($delimiter, $name);
                $newWords = [];
                foreach ($words as $word) {
                    $newWords[] = ucfirst($word);
                }
                $name = implode($delimiter, $newWords);
            }
            if (is_array($values)) {
                foreach ($values as $value) {
                    $content .= sprintf("%-{$max}s %s\r\n", $name . ':', $value);
                }
            }
        }

        return $content;
    }

    /**
     * Returns the headers.
     *
     * @param ?string $key The name of the headers to return or null to get them all
     * @return string[]|string[][]
     */
    public function all(?string $key = null)
    {
        if ($key !== null) {
            return $this->headers[strtr($key, self::UPPER, self::LOWER)] ?? [];
        }

        return $this->headers;
    }

    /**
     * Returns the parameter keys.
     *
     * @return string[]
     */
    public function keys()
    {
        return array_map('strval', array_keys($this->all()));
    }

    /**
     * Replaces the current HTTP headers by a new set.
     * @param string[][]|string[] $headers
     */
    public function replace($headers = []): void
    {
        $this->headers = [];
        $this->add($headers);
    }

    /**
     * Adds new headers the current HTTP headers set.
     * @param string[][]|string[] $headers
     */
    public function add($headers): void
    {
        foreach ($headers as $key => $values) {
            $this->set((string) $key, $values);
        }
    }

    /**
     * Returns the first header by name or the default one.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $headers = $this->all($key);

        if ($headers === []) {
            return $default;
        }

        if ($headers[0] === '') {
            return null;
        }

        if (is_array($headers[0])) {
            /** @phpstan-ignore-next-line */
            return (string) $headers[0][0];
        }

        /** @phpstan-ignore-next-line */
        return (string) $headers[0];
    }

    /**
     * Sets a header by name.
     *
     * @param mixed                $values  The value or an array of values
     * @param bool                 $replace Whether to replace the actual value or not (true by default)
     */
    public function set(string $key, $values, bool $replace = true): void
    {
        $key = strtr($key, self::UPPER, self::LOWER);

        if (is_array($values)) {
            /** @var string[] $strValues */
            $strValues = array_map('strval', array_values($values));

            if ($replace === true || !array_key_exists($key, $this->headers)) {
                $this->headers[$key] = $strValues;
            } else {
                $this->headers[$key] = array_merge($this->headers[$key], $strValues);
            }
        } elseif ($replace === true || !array_key_exists($key, $this->headers)) {
            $this->headers[$key] = [(string) $values];
        } else {
            $this->headers[$key][] = (string) $values;
        }

        if ($key === 'cache-control') {
            $this->cacheControl = $this->parseCacheControl(implode(', ', $this->headers[$key]));
        }
    }

    /**
     * Returns true if the HTTP header is defined.
     */
    public function has(string $key): bool
    {
        return \array_key_exists(strtr($key, self::UPPER, self::LOWER), $this->all());
    }

    /**
     * Returns true if the given HTTP header contains the given value.
     */
    public function contains(string $key, string $value): bool
    {
        return in_array($value, $this->all($key), true);
    }

    /**
     * Removes a header.
     */
    public function remove(string $key): void
    {
        $key = strtr($key, self::UPPER, self::LOWER);

        unset($this->headers[$key]);

        if ($key === 'cache-control') {
            $this->cacheControl = [];
        }
    }

    /**
     * Returns the HTTP header value converted to a date.
     *
     * @throws RuntimeException When the HTTP header is not parseable
     */
    public function getDate(string $key, ?DateTime $default = null): ?DateTime
    {
        $value = $this->get($key);

        if ($value === null) {
            return $default;
        }

        // The KPHP create from format function returns null|\DateTime
        $date = DateTime::createFromFormat(\DATE_RFC2822, $value);
        /** @phpstan-ignore-next-line */
        if (!isset($date)) {
            throw new RuntimeException(sprintf('The "%s" HTTP header is not parseable (%s).', $key, $value));
        }

        // As I said, $date is null|\DateTime
        /** @phpstan-ignore-next-line */
        return $date;
    }

    /**
     * Adds a custom Cache-Control directive.
     * @param boolean|string|int $value
     */
    public function addCacheControlDirective(string $key, $value = true): void
    {
        if (is_bool($value)) {
            $this->cacheControl[$key] = $value;
        } else {
            $this->cacheControl[$key] = (string) $value;
        }

        $this->set('Cache-Control', $this->getCacheControlHeader());
    }

    /**
     * Returns true if the Cache-Control directive is defined.
     */
    public function hasCacheControlDirective(string $key): bool
    {
        return \array_key_exists($key, $this->cacheControl);
    }

    /**
     * Returns a Cache-Control directive value by name.
     *
     * @return boolean|string|null
     */
    public function getCacheControlDirective(string $key)
    {
        return $this->cacheControl[$key] ?? null;
    }

    /**
     * Removes a Cache-Control directive.
     */
    public function removeCacheControlDirective(string $key): void
    {
        unset($this->cacheControl[$key]);

        $this->set('Cache-Control', $this->getCacheControlHeader());
    }

    protected function getCacheControlHeader(): string
    {
        ksort($this->cacheControl);

        return HeaderUtils::toString($this->cacheControl, ',');
    }

    /**
     * Parses a Cache-Control HTTP header.
     *
     * @return string[]|bool[]
     */
    protected function parseCacheControl(string $header)
    {
        $parts = HeaderUtils::split($header, ',=');

        return HeaderUtils::combine($parts);
    }
}
