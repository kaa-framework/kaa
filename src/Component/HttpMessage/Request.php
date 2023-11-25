<?php

declare(strict_types=1);

namespace Kaa\Component\HttpMessage;

class Request
{
    /**
     * Query string parameters ($_GET).
     */
    public InputBag $query;

    /**
     * Request body parameters ($_POST).
     */
    public InputBag $request;

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

    /**
     * @param string[]             $query      The GET parameters
     * @param string[]             $request    The POST parameters
     * @param string[]             $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param string[]             $server     The SERVER parameters
     * @param string|false         $content    The raw body data
     */
    public function __construct(
        $query = [],
        $request = [],
        $attributes = [],
        $server = [],
        $content = false,
    ) {
        $this->initialize($query, $request, $attributes, $server, $content);
    }

    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param string[]             $query      The GET parameters
     * @param string[]             $request    The POST parameters
     * @param string[]             $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param string[]             $server     The SERVER parameters
     * @param string|false         $content    The raw body data
     */
    public function initialize($query = [], $request = [], $attributes = [], $server = [], $content = false): void
    {
        $this->query = new InputBag($query);
        $this->request = new InputBag($request);
        $this->attributes = new ParameterBag($attributes);
        $this->server = new ServerBag($server);
        $this->headers = new HeaderBag($this->server->getHeaders());
        $this->content = $content;
    }

    /**
     * Returns the request body content.
     * @return string|false
     */
    public function getContent()
    {
        if (!isset($this->content)) {
            $fileGetContents = file_get_contents('php://input');
            if ($fileGetContents !== false) {
                $this->content = $fileGetContents;
            }
        }

        return $this->content;
    }
}
