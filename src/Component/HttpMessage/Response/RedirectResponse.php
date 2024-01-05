<?php

declare(strict_types=1);

namespace Kaa\Component\HttpMessage\Response;

class RedirectResponse extends Response
{
    private string $url;

    public function __construct(string $redirectUrl)
    {
        parent::__construct();
        $this->url = $redirectUrl;
    }

    public function send(): void
    {
        header('Location: ' . $this->url);
    }
}
