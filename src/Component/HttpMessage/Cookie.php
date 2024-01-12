<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kaa\Component\HttpMessage;

use DateTimeInterface;
use InvalidArgumentException;
use Kaa\HttpFoundation\HeaderUtils;

/**
 * This file has been rewritten for KPHP compilation.
 * Please refer to the original Symfony HttpFoundation repository for the original source code.
 * @see https://github.com/symfony/http-foundation
 * @author Mikhail Fedosov <fedosovmichael@gmail.com>
 *
 * Represents a cookie.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Cookie
{
    public const SAMESITE_NONE = 'none';
    public const SAMESITE_LAX = 'lax';
    public const SAMESITE_STRICT = 'strict';

    private string $name;

    private ?string $value;

    private ?string $domain;

    private int $expire;

    private string $path;

    private ?bool $secure;

    private bool $httpOnly;

    private bool $raw;

    private ?string $sameSite = null;

    private bool $secureDefault = false;
    private const RESERVED_CHARS_LIST = "=,; \t\r\n\v\f";
    private const RESERVED_CHARS_FROM = ['=', ',', ';', ' ', "\t", "\r", "\n", "\v", "\f"];
    private const RESERVED_CHARS_TO = ['%3D', '%2C', '%3B', '%20', '%09', '%0D', '%0A', '%0B', '%0C'];

    /**
     * Creates cookie from raw header string.
     */
    public static function fromString(string $cookie, bool $decode = false): self
    {
        $data = [
            'expires' => 0,
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => false,
            'raw' => !$decode,
            'samesite' => null,
        ];

        $parts = HeaderUtils::split($cookie, ';=');
        $part = array_shift($parts);

        /** @phpstan-ignore-next-line */
        $name = $decode ? urldecode($part[0]) : (string) $part[0];
        /** @phpstan-ignore-next-line */
        $value = array_key_exists(1, $part) ? ($decode ? urldecode($part[1]) : (string) $part[1]) : null;

        $data = HeaderUtils::combine($parts) + $data;
        $data['expires'] = self::expiresTimestamp($data['expires']);

        if (array_key_exists('max-age', $data) && ($data['max-age'] > 0 || $data['expires'] > time())) {
            $data['expires'] = time() + (int) $data['max-age'];
        }

        /** @phpstan-ignore-next-line */
        return new static(
            $name,
            $value,
            (int) $data['expires'], /** @phpstan-ignore-line */
            (string) $data['path'],
            $data['domain'] !== null ? (string) $data['domain'] : null,
            $data['secure'] !== null ? (bool) $data['secure'] : null,
            (bool) $data['httponly'],
            (bool) $data['raw'],
            $data['samesite'] !== null ? (string) $data['samesite'] : null
        );
    }

    /**
     * @param string                        $name     The name of the cookie
     * @param ?string                       $value    The value of the cookie
     * @param ?DateTimeInterface            $expire   The time the cookie expires
     * @param string                        $path     The path on the server in which the cookie will be available on
     * @param ?string                       $domain   The domain that the cookie is available to
     * @param ?bool                         $secure   Whether the client should send back the cookie only over HTTPS or
     *                                                null to auto-enable this when the request is already using HTTPS
     * @param bool                          $httpOnly Whether the cookie will be made accessible
     *                                                only through the HTTP protocol
     * @param bool                          $raw      Whether the cookie value should be sent with no url encoding
     *@see self::__construct
     */
    public static function createWithExpiresDateTime(
        string $name,
        ?string $value = null,
        ?DateTimeInterface $expire = null,
        string $path = '/',
        ?string $domain = null,
        ?bool $secure = null,
        bool $httpOnly = true,
        bool $raw = false,
        ?string $sameSite = self::SAMESITE_LAX
    ): self {
        $expireTimestamp = $expire !== null ? $expire->getTimestamp() : null;

        return new self($name, $value, $expireTimestamp, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * @see self::__construct
     *
     * @param string                        $name     The name of the cookie
     * @param ?string                       $value    The value of the cookie
     *                                      $expire   The time the cookie expires
     * @param string                        $path     The path on the server in which the cookie will be available on
     * @param ?string                       $domain   The domain that the cookie is available to
     * @param ?bool                         $secure   Whether the client should send back the cookie only over HTTPS or
     *                                                null to auto-enable this when the request is already using HTTPS
     * @param bool                          $httpOnly Whether the cookie will be made accessible
     *                                                only through the HTTP protocol
     * @param bool                          $raw      Whether the cookie value should be sent with no url encoding
     */
    public static function create(
        string $name,
        ?string $value = null,
        int|string $expire = 0,
        string $path = '/',
        ?string $domain = null,
        ?bool $secure = null,
        bool $httpOnly = true,
        bool $raw = false,
        ?string $sameSite = self::SAMESITE_LAX
    ): self {
        if (is_string($expire)) {
            $expireInt = self::expiresTimestamp($expire);
        } else {
            $expireInt = (int) $expire;
        }

        return new self($name, $value, $expireInt, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * @param string                        $name     The name of the cookie
     * @param ?string                       $value    The value of the cookie
     * @param ?int                          $expire   The time the cookie expires
     * @param string                        $path     The path on the server in which the cookie will be available on
     * @param ?string                       $domain   The domain that the cookie is available to
     * @param ?bool                         $secure   Whether the client should send back the cookie only over HTTPS or
     *                                                null to auto-enable this when the request is already using HTTPS
     * @param bool                          $httpOnly Whether the cookie will be made accessible
     *                                                only through the HTTP protocol
     * @param bool                          $raw      Whether the cookie value should be sent with no url encoding
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $name,
        ?string $value = null,
        ?int $expire = 0,
        string $path = '/',
        ?string $domain = null,
        ?bool $secure = null,
        bool $httpOnly = true,
        bool $raw = false,
        ?string $sameSite = self::SAMESITE_LAX
    ) {
        // from PHP source code
        if ($raw && strpbrk($name, self::RESERVED_CHARS_LIST) !== false) {
            throw new InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if (empty($name)) {
            throw new InvalidArgumentException('The cookie name cannot be empty.');
        }

        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->expire = self::expiresTimestamp($expire);
        $this->path = empty($path) ? '/' : $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->raw = $raw;
        $this->sameSite = $this->withSameSite($sameSite)->sameSite;
    }

    /**
     * Creates a cookie copy with a new value.
     */
    public function withValue(?string $value): self
    {
        $cookie = clone $this;
        $cookie->value = $value;

        return $cookie;
    }

    /**
     * Creates a cookie copy with a new domain that the cookie is available to.
     */
    public function withDomain(?string $domain): self
    {
        $cookie = clone $this;
        $cookie->domain = $domain;

        return $cookie;
    }

    /**
     * Creates a cookie copy with a new time the cookie expires.
     */
    public function withExpires(int|string $expire = 0): self
    {
        $cookie = clone $this;
        $cookie->expire = self::expiresTimestamp($expire);

        return $cookie;
    }

    /**
     * Creates a cookie copy with a new time the cookie expires.
     */
    public function withExpiresDateTime(DateTimeInterface $expire): self
    {
        $cookie = clone $this;
        $cookie->expire = self::expiresTimestamp($expire->format('U'));

        return $cookie;
    }

    /**
     * Converts expires formats to a unix timestamp.
     *
     * @param mixed $expire
     */
    private static function expiresTimestamp($expire = 0): int
    {
        // convert expiration time to a Unix timestamp
        if (!is_numeric($expire)) {
            $expireInt = strtotime($expire);

            if ($expireInt === false) {
                throw new InvalidArgumentException('The cookie expiration time is not valid.');
            }
        } else {
            $expireInt = $expire;
        }

        if ($expireInt > 0) {
            return (int) $expireInt;
        }

        return 0;
    }

    /**
     * Creates a cookie copy with a new path on the server in which the cookie will be available on.
     */
    public function withPath(string $path): self
    {
        $cookie = clone $this;
        if ($path === '') {
            $cookie->path = '/';
        } else {
            $cookie->path = $path;
        }

        return $cookie;
    }

    /**
     * Creates a cookie copy that only be transmitted over a secure HTTPS connection from the client.
     */
    public function withSecure(bool $secure = true): self
    {
        $cookie = clone $this;
        $cookie->secure = $secure;

        return $cookie;
    }

    /**
     * Creates a cookie copy that be accessible only through the HTTP protocol.
     */
    public function withHttpOnly(bool $httpOnly = true): self
    {
        $cookie = clone $this;
        $cookie->httpOnly = $httpOnly;

        return $cookie;
    }

    /**
     * Creates a cookie copy that uses no url encoding.
     */
    public function withRaw(bool $raw = true): static
    {
        if ($raw && strpbrk($this->name, self::RESERVED_CHARS_LIST) !== false) {
            throw new InvalidArgumentException(
                sprintf('The cookie name "%s" contains invalid characters.', $this->name)
            );
        }

        $cookie = clone $this;
        $cookie->raw = $raw;

        return $cookie;
    }

    /**
     * Creates a cookie copy with SameSite attribute.
     */
    public function withSameSite(?string $sameSite): static
    {
        if ($sameSite === '') {
            $sameSite = null;
        } elseif ($sameSite !== null) {
            $sameSite = strtolower($sameSite);
        }

        if (!in_array($sameSite, [self::SAMESITE_LAX, self::SAMESITE_STRICT, self::SAMESITE_NONE, null], true)) {
            throw new InvalidArgumentException('The "sameSite" parameter value is not valid.');
        }

        $cookie = clone $this;
        $cookie->sameSite = $sameSite;

        return $cookie;
    }

    /**
     * Returns the cookie as a string.
     */
    public function __toString(): string
    {
        if ($this->isRaw()) {
            $str = $this->getName();
        } else {
            $count = 0;
            $str = str_replace(
                self::RESERVED_CHARS_FROM,
                self::RESERVED_CHARS_TO,
                $this->getName(),
                $count
            );
        }

        $str .= '=';

        if ((string) $this->getValue() === '') {
            $str .= 'deleted; expires=' . gmdate('D, d M Y H:i:s T', time() - 31536001) . '; Max-Age=0';
        } else {
            $str .= $this->isRaw() ? (string) $this->getValue() : rawurlencode((string) $this->getValue());

            if ($this->getExpiresTime() !== 0) {
                $str .=
                    '; expires=' . gmdate('D, d M Y H:i:s T', $this->getExpiresTime()) .
                    '; Max-Age=' . $this->getMaxAge();
            }
        }

        if (strlen($this->getPath()) !== 0) {
            $str .= '; path=' . $this->getPath();
        }

        if ($this->getDomain() !== null && strlen($this->getDomain()) !== 0) {
            $str .= '; domain=' . $this->getDomain();
        }

        if ($this->isSecure() === true) {
            $str .= '; secure';
        }

        if ($this->isHttpOnly() === true) {
            $str .= '; httponly';
        }

        if ($this->getSameSite() !== null) {
            $str .= '; samesite=' . $this->getSameSite();
        }

        return $str;
    }

    /**
     * Gets the name of the cookie.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the value of the cookie.
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Gets the domain that the cookie is available to.
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Gets the time the cookie expires.
     */
    public function getExpiresTime(): int
    {
        return $this->expire;
    }

    /**
     * Gets the max-age attribute.
     */
    public function getMaxAge(): int
    {
        $maxAge = $this->expire - time();

        return max(0, $maxAge);
    }

    /**
     * Gets the path on the server in which the cookie will be available on.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Checks whether the cookie should only be transmitted over a secure HTTPS connection from the client.
     */
    public function isSecure(): bool
    {
        return $this->secure ?? $this->secureDefault;
    }

    /**
     * Checks whether the cookie will be made accessible only through the HTTP protocol.
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * Whether this cookie is about to be cleared.
     */
    public function isCleared(): bool
    {
        return $this->expire !== 0 && $this->expire < time();
    }

    /**
     * Checks if the cookie value should be sent with no url encoding.
     */
    public function isRaw(): bool
    {
        return $this->raw;
    }

    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }

    /**
     * @param bool $default The default value of the "secure" flag when it is set to null
     */
    public function setSecureDefault(bool $default): void
    {
        $this->secureDefault = $default;
    }
}
