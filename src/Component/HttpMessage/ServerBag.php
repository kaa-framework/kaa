<?php

declare(strict_types=1);

namespace Kaa\Component\HttpMessage;

class ServerBag extends ParameterBag
{
    /**
     * Gets the HTTP headers.
     * @return string[]
     */
    public function getHeaders()
    {
        /** @var string[] $headers */
        $headers = [];
        foreach ($this->parameters as $key => $value) {
            // $key is always string
            $key = (string) $key;
            if (str_starts_with($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $headers[$key] = $value;
            }
        }

        if (array_key_exists('PHP_AUTH_USER', $this->parameters)) {
            $headers['PHP_AUTH_USER'] = $this->parameters['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW'] = $this->parameters['PHP_AUTH_PW'] ?? '';
        } else {
            /** @var ?string $authorizationHeader */
            $authorizationHeader = null;
            if (array_key_exists('HTTP_AUTHORIZATION', $this->parameters)) {
                $authorizationHeader = $this->parameters['HTTP_AUTHORIZATION'];
            } elseif (array_key_exists('REDIRECT_HTTP_AUTHORIZATION', $this->parameters)) {
                $authorizationHeader = $this->parameters['REDIRECT_HTTP_AUTHORIZATION'];
            }

            if (is_string($authorizationHeader)) {
                if (stripos($authorizationHeader, 'basic ') === 0) {
                    // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
                    $exploded = explode(':', (string) base64_decode(substr($authorizationHeader, 6), true), 2);
                    if (count($exploded) === 2) {
                        [$headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']] = $exploded;
                    }
                } elseif (
                    /** @phpstan-ignore-next-line */
                    empty($this->parameters['PHP_AUTH_DIGEST']) &&
                    (stripos($authorizationHeader, 'digest ') === 0)
                ) {
                    // In some circumstances PHP_AUTH_DIGEST needs to be set
                    $headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
                    $this->parameters['PHP_AUTH_DIGEST'] = $authorizationHeader;
                } elseif (stripos($authorizationHeader, 'bearer ') === 0) {
                    /*
                     * XXX: Since there is no PHP_AUTH_BEARER in PHP predefined variables,
                     *      I'll just set $headers['AUTHORIZATION'] here.
                     *      https://php.net/reserved.variables.server
                     */
                    $headers['AUTHORIZATION'] = $authorizationHeader;
                }
            }
        }

        if (array_key_exists('AUTHORIZATION', $headers)) {
            return $headers;
        }

        // PHP_AUTH_USER/PHP_AUTH_PW
        if (array_key_exists('PHP_AUTH_USER', $headers)) {
            $headers['AUTHORIZATION'] = 'Basic ' . base64_encode($headers['PHP_AUTH_USER']
                    . ':' . ($headers['PHP_AUTH_PW'] ?? ''));
        } elseif (array_key_exists('PHP_AUTH_DIGEST', $headers)) {
            $headers['AUTHORIZATION'] = $headers['PHP_AUTH_DIGEST'];
        }

        return $headers;
    }
}
