<?php

declare(strict_types=1);

namespace Kaa\Component\HttpMessage;

use Kaa\Component\HttpMessage\Exception\BadRequestException;
use Kaa\Component\HttpMessage\Exception\NoContentException;
use Kaa\Component\HttpMessage\Exception\SuspiciousOperationException;

class Request
{
    // In KPHP, there is not yet a predefined constant directory_separator
    public const DIRECTORY_SEPARATOR = '/';

    /**
     * Query string parameters ($_GET).
     */
    public InputBag $query;

    /**
     * Request body parameters ($_POST).
     */
    public InputBag $request;

    public InputBag $cookie;

    private static bool $httpMethodParameterOverride = false;

    /**
     * Custom parameters.
     */
    public ParameterBag $attributes;

    /**
     * Server and execution environment parameters ($_SERVER).
     */
    public ServerBag $server;

    /**
     * Headers (taken from the $_SERVER).
     */
    public HeaderBag $headers;

    private false|string $content;

    private ?string $pathInfo = null;

    private ?string $requestUri = null;

    private ?string $baseUrl = null;

    private ?string $method = null;

    /**
     * @param string[] $query The GET parameters
     * @param string[] $request The POST parameters
     * @param string[] $cookie The POST parameters
     * @param string[] $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param string[] $server The SERVER parameters
     * @param string|false $content The raw body data
     */
    public function __construct(
        $query = [],
        $request = [],
        $cookie = [],
        $attributes = [],
        $server = [],
        $content = false
    ) {
        $this->initialize($query, $request, $cookie, $attributes, $server, $content);
    }

    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param string[] $query The GET parameters
     * @param string[] $request The POST parameters
     * @param string[] $cookie The COOKIE parameters
     * @param string[] $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param string[] $server The SERVER parameters
     * @param string|false $content The raw body data
     */
    public function initialize(
        array $query = [],
        array $request = [],
        array $cookie = [],
        array $attributes = [],
        array $server = [],
        string|false $content = false
    ): void {
        $this->query = new InputBag($query);
        $this->request = new InputBag($request);
        $this->cookie = new InputBag($cookie);
        $this->attributes = new ParameterBag($attributes);
        $this->server = new ServerBag($server);
        $this->headers = new HeaderBag($this->server->getHeaders());
        $this->content = $content;
    }

    /**
     * Creates a Request based on a given URI and configuration.
     *
     * The information contained in the URI always take precedence
     * over the other information (server and parameters).
     *
     * @param string $uri The URI
     * @param string $method The HTTP method
     * @param string[] $parameters The query (GET) or request (POST) parameters
     * @param string[] $server The server parameters ($_SERVER)
     * @param string|false $content The raw body data
     */
    public static function create(
        $uri,
        $method = 'GET',
        $parameters = [],
        $server = [],
        $content = false
    ): self {
        // It does not make sense to put the $server variable as a ServerConfig class.
        // Let's just convert everything to a string[] array
        $server = array_replace([
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => '80',
            'HTTP_HOST' => 'localhost',
            'HTTP_USER_AGENT' => 'Symfony',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR' => '127.0.0.1',
            'SCRIPT_NAME' => '',
            'SCRIPT_FILENAME' => '',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_TIME' => (string) time(),
            'REQUEST_TIME_FLOAT' => (string) microtime(true),
        ], $server);

        $server['PATH_INFO'] = '';
        $server['REQUEST_METHOD'] = strtoupper($method);

        $components = parse_url($uri);

        if (is_array($components)) {
            $components = array_map('strval', $components);
        } else {
            $components = [];
        }

        if (array_key_exists('host', $components)) {
            $server['SERVER_NAME'] = $components['host'];
            $server['HTTP_HOST'] = $components['host'];
        }

        if (array_key_exists('scheme', $components)) {
            if ($components['scheme'] === 'https') {
                $server['HTTPS'] = 'on';
                $server['SERVER_PORT'] = '443';
            } else {
                unset($server['HTTPS']);
                $server['SERVER_PORT'] = '80';
            }
        }

        if (array_key_exists('port', $components)) {
            $server['SERVER_PORT'] = $components['port'];
            $server['HTTP_HOST'] .= ':' . $components['port'];
        }

        if (array_key_exists('user', $components)) {
            $server['PHP_AUTH_USER'] = $components['user'];
        }

        if (array_key_exists('pass', $components)) {
            $server['PHP_AUTH_PW'] = $components['pass'];
        }

        if (!array_key_exists('path', $components)) {
            $components['path'] = '/';
        }

        switch (strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                if (!array_key_exists('CONTENT_TYPE', $server)) {
                    $server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
                }

                // no break
            case 'PATCH':
                $request = $parameters;
                $query = [];
                break;
            default:
                $request = [];
                $query = $parameters;
                break;
        }

        $queryString = '';
        if (array_key_exists('query', $components)) {
            parse_str(html_entity_decode($components['query']), $qs);

            $qs = array_map(function ($item) {
                if (is_array($item)) {
                    return json_encode($item); // or any other logic to handle arrays
                }

                return strval($item);
            }, $qs);

            if ((bool) $query) {
                $query = array_replace($qs, $query);
                $queryString = http_build_query($query, '', '&');
            } else {
                $query = $qs;
                $queryString = $components['query'];
            }
        } elseif ((bool) $query) {
            $queryString = http_build_query($query, '', '&');
        }

        $server['REQUEST_URI'] = $components['path'] . ($queryString !== '' ? '?' . $queryString : '');
        $server['QUERY_STRING'] = $queryString;

        /** @phpstan-ignore-next-line */
        return new static($query, $request, [], [], $server, $content);
    }

    /**
     * Creates a new request with values from PHP's super globals.
     */
    public static function createFromGlobals(): static
    {
        /** @var string[] $getArray */
        $getArray = array_map('strval', $_GET);

        /** @var string[] $postArray */
        $postArray = array_map('strval', $_POST);

        /** @var string[] $cookiesArray */
        $cookiesArray = array_map('strval', $_COOKIE);

        /** @var mixed $serverStringValues */
        $serverStringValues = array_filter($_SERVER, static function ($value) {
            return !\is_array($value);
        });

        /** @var string[] $serverArray */
        $serverArray = array_map('strval', $serverStringValues);

        $request = new static($getArray, $postArray, $cookiesArray, [], $serverArray, not_false(file_get_contents('php://input')));

        $headerString = $request->headers->get('CONTENT_TYPE', '');

        if (
            isset($headerString) && str_starts_with($headerString, 'application/x-www-form-urlencoded')
            && \in_array(
                strtoupper((string) $request->server->get('REQUEST_METHOD', 'GET')),
                ['PUT', 'DELETE', 'PATCH'],
                true
            )
        ) {
            parse_str((string) $request->getContent(), $data);
            $request->request = new InputBag($data);
        }

        return $request;
    }

    /**
     * Returns the request body content.
     * @throws NoContentException
     */
    public function getContent(): string
    {
        if (!isset($this->content)) {
            $fileGetContents = file_get_contents('php://input');
            if ($fileGetContents !== false) {
                $this->content = $fileGetContents;
            }
        }

        if ($this->content === false) {
            throw new NoContentException('The request does not have content');
        }

        return not_false($this->content);
    }

    /*
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (https://framework.zend.com/license).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (https://www.zend.com/)
     */

    private function prepareRequestUri(): string
    {
        $requestUri = '';

        if (
            $this->server->get('IIS_WasUrlRewritten') === '1'
            && $this->server->get('UNENCODED_URL') !== ''
        ) {
            // IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
            $requestUri = $this->server->get('UNENCODED_URL', '');
            $this->server->remove('UNENCODED_URL');
            $this->server->remove('IIS_WasUrlRewritten');
        } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = (string) $this->server->get('REQUEST_URI', '');

            if (strlen($requestUri) !== 0 && $requestUri[0] === '/') {
                // To only use path and query remove the fragment.
                $pos = strpos($requestUri, '#');
                if ($pos !== false) {
                    $requestUri = substr($requestUri, 0, $pos);
                }
            } else {
                // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
                // only use URL path.
                /** @var mixed $uriComponents2 */
                $uriComponents2 = parse_url($requestUri);
                /** @var string[] $uriComponents */
                $uriComponents = array_map('strval', $uriComponents2);

                if (array_key_exists('path', $uriComponents)) {
                    $requestUri = $uriComponents['path'];
                }

                if (array_key_exists('query', $uriComponents)) {
                    $requestUri .= '?' . $uriComponents['query'];
                }
            }
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = (string) $this->server->get('ORIG_PATH_INFO', '');
            if ($this->server->get('QUERY_STRING') !== '') {
                $requestUri .= '?' . $this->server->get('QUERY_STRING', '');
            }
            $this->server->remove('ORIG_PATH_INFO');
        }

        // normalize the request URI to ease creating sub-requests from this request
        $this->server->set('REQUEST_URI', (string) $requestUri);

        return (string) $requestUri;
    }

    /**
     * Returns the requested URI (path and query string).
     *
     * @return ?string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri(): ?string
    {
        return $this->requestUri ??= $this->prepareRequestUri();
    }

    /**
     * Returns the path being requested relative to the executed script.
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
     *  * http://localhost/mysite/about?var=1  returns '/about'
     *
     * @return string The raw path (i.e. not urldecoded)
     */
    public function getPathInfo(): string
    {
        $this->pathInfo ??= $this->preparePathInfo();

        return not_null($this->pathInfo);
    }

    /**
     * Returns the prefix as encoded in the string when the string starts with
     * the given prefix, null otherwise.
     */
    private function getUrlencodedPrefix(?string $string, ?string $prefix): ?string
    {
        if (
            !(isset($string) && isset($prefix))
            || !str_starts_with(rawurldecode($string), $prefix)
        ) {
            return null;
        }

        $len = \strlen($prefix);

        if ((bool) preg_match(sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $string, $match)) {
            return (string) $match[0];
        }

        return null;
    }

    /**
     * Prepares the base URL.
     */
    private function prepareBaseUrl(): string
    {
        $filename = basename((string) $this->server->get('SCRIPT_FILENAME', ''));

        if (basename((string) $this->server->get('SCRIPT_NAME', '')) === $filename) {
            $baseUrl = $this->server->get('SCRIPT_NAME');
        } elseif (basename((string) $this->server->get('PHP_SELF', '')) === $filename) {
            $baseUrl = $this->server->get('PHP_SELF');
        } elseif (basename((string) $this->server->get('ORIG_SCRIPT_NAME', '')) === $filename) {
            $baseUrl = $this->server->get('ORIG_SCRIPT_NAME'); // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = (string) $this->server->get('PHP_SELF', '');
            $file = (string) $this->server->get('SCRIPT_FILENAME', '');
            $segs = explode('/', trim($file, '/'));
            $segs = array_reverse($segs);
            $index = 0;
            $last = count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/' . $seg . $baseUrl;
                $index++;
                $pos = strpos($path, $baseUrl);
            } while ($last > $index && ($pos !== false) && $pos !== 0);
        }

        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = (string) $this->getRequestUri();
        if (strlen($requestUri) !== 0 && $requestUri[0] !== '/') {
            $requestUri = '/' . $requestUri;
        }

        $prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl);
        if ((bool) $baseUrl && $prefix !== null) {
            // full $baseUrl matches
            return $prefix;
        }

        $prefix = $this->getUrlencodedPrefix(
            $requestUri,
            rtrim(
                dirname((string) $baseUrl),
                '/' . self::DIRECTORY_SEPARATOR,
            ) . '/',
        );
        if ((bool) $baseUrl && $prefix !== null) {
            // directory portion of $baseUrl matches
            return rtrim($prefix, '/' . self::DIRECTORY_SEPARATOR);
        }

        $truncatedRequestUri = $requestUri;
        $pos = strpos($requestUri, '?');
        if ($pos !== false) {
            $truncatedRequestUri = substr($requestUri, 0, $pos);
        }

        $basename = basename($baseUrl ?? '');
        if ($basename === '' || !(bool) strpos(rawurldecode($truncatedRequestUri), $basename)) {
            // no match whatsoever; set it blank
            return '';
        }

        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        $baseUrl = (string) $baseUrl;
        $pos = strpos($requestUri, $baseUrl);
        if (\strlen($requestUri) >= \strlen($baseUrl) && ($pos !== false) && $pos !== 0) {
            $baseUrl = substr($requestUri, 0, $pos + \strlen($baseUrl));
        }

        return rtrim($baseUrl, '/' . self::DIRECTORY_SEPARATOR);
    }

    /**
     * Returns the real base URL received by the webserver from which this request is executed.
     * The URL does not include trusted reverse proxy prefix.
     *
     * @return string The raw URL (i.e. not urldecoded)
     */
    private function getBaseUrlReal(): string
    {
        if ($this->baseUrl === null) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return not_null($this->baseUrl);
    }

    /**
     * Prepares the path info.
     */
    private function preparePathInfo(): string
    {
        $requestUri = (string) $this->getRequestUri();

        if ($requestUri === '') {
            return '/';
        }

        // Remove the query string from REQUEST_URI
        $pos = strpos($requestUri, '?');
        if ($pos !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        if (strlen($requestUri) !== 0 && $requestUri[0] !== '/') {
            $requestUri = '/' . $requestUri;
        }

        $baseUrl = $this->getBaseUrlReal();

        $pathInfo = substr($requestUri, \strlen($baseUrl));
        if (!(bool) $pathInfo || $pathInfo === '') {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        }

        return $pathInfo;
    }

    /**
     * Sets the request method.
     */
    public function setMethod(string $method): void
    {
        $this->method = null;
        $this->server->set('REQUEST_METHOD', $method);
    }

    /**
     * Gets the request "intended" method.
     *
     * If the X-HTTP-Method-Override header is set, and if the method is a POST,
     * then it is used to determine the "real" intended HTTP method.
     *
     * The _method request parameter can also be used to determine the HTTP method,
     * but only if enableHttpMethodParameterOverride() has been called.
     *
     * The method is always an uppercased string.
     *
     * @throws BadRequestException|SuspiciousOperationException
     *
     * @see getRealMethod()
     */
    public function getMethod(): ?string
    {
        if ($this->method !== null) {
            return $this->method;
        }

        $this->method = strtoupper((string) $this->server->get('REQUEST_METHOD', 'GET'));

        if ($this->method !== 'POST') {
            return $this->method;
        }

        $method = $this->headers->get('X-HTTP-METHOD-OVERRIDE');

        if (!(bool) $method && self::$httpMethodParameterOverride) {
            $method = $this->request->get('_method', (string) $this->query->get('_method', 'POST'));
        }

        if (!is_string($method)) {
            return $this->method;
        }

        $method = strtoupper($method);

        if (
            in_array(
                $method,
                [
                    'GET',
                    'HEAD',
                    'POST',
                    'PUT',
                    'DELETE',
                    'CONNECT',
                    'OPTIONS',
                    'PATCH',
                    'PURGE',
                    'TRACE'
                ],
                true,
            )
        ) {
            return $this->method = $method;
        }

        if (!(bool) preg_match('/^[A-Z]++$/D', $method, $matches)) {
            throw new SuspiciousOperationException(sprintf('Invalid method override "%s".', $method));
        }

        return $this->method = $method;
    }
}
