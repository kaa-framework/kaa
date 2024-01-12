<?php

declare(strict_types=1);

namespace Kaa\Component\HttpMessage\Response;

use InvalidArgumentException;
use Kaa\Component\HttpMessage\Cookie;
use Kaa\Component\HttpMessage\HttpCode;

class Response
{
    /** @var string[] */
    protected array $headers;

    protected string $content;

    protected string $version;

    protected int $statusCode;

    protected string $statusText = '';

    /** @var Cookie[] */
    protected array $cookies = [];

    /**
     * @param string[] $headers
     * @throws InvalidArgumentException When the HTTP status code is not valid
     */
    public function __construct(?string $content = '', int $status = HttpCode::HTTP_OK, array $headers = [])
    {
        $this->headers = $headers;
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
    }

    public function addCookie(Cookie $cookie): self
    {
        $this->cookies[] = $cookie;

        return $this;
    }

    /**
     * Sets the response content.
     */
    public function setContent(?string $content): self
    {
        $this->content = $content ?? '';

        return $this;
    }

    /**
     * Sets the HTTP protocol version (1.0 or 1.1).
     */
    public function setProtocolVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Retrieves the status code for the current web response.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Sets the response status code.
     *
     * If the status text is null it will be automatically populated for the known
     * status codes and left empty otherwise.
     *
     * @throws InvalidArgumentException When the HTTP status code is not valid
     */
    public function setStatusCode(int $code, string $text = null): self
    {
        $this->statusCode = $code;
        if ($code < 100 || $code >= 600) {
            throw new InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }

        if ($text === null) {
            $text = HttpCode::STATUS_TEXTS[$code] ?? 'unknown status';
        }

        $this->statusText = not_null($text);

        return $this;
    }

    /**
     * Gets the HTTP protocol version.
     */
    public function getProtocolVersion(): string
    {
        return $this->version;
    }

    public function send(): void
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    /**
     * Sends HTTP headers.
     */
    public function sendHeaders(): self
    {
        foreach ($this->headers as $header) {
            header($header, false, $this->statusCode);
        }

        foreach ($this->cookies as $cookie) {
            header('Set-Cookie: ' . $cookie, false, $this->statusCode);
        }

        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);

        return $this;
    }

    /**
     * Sends content for the current web response.
     */
    public function sendContent(): self
    {
        echo $this->content;

        return $this;
    }
}
