<?php

namespace Kaa\Bundle\KTemplate;

use KTemplate\Context;
use KTemplate\Engine;
use KTemplate\FilesystemLoader;

class KTemplateFactory
{
    private Context $context;
    private FilesystemLoader $filesystemLoader;
    private string $url;

    public function __construct(
        Context $context,
        FilesystemLoader $filesystemLoader,
        string $url,
    ) {
        $this->context = $context;
        $this->filesystemLoader = $filesystemLoader;
        $this->url = $url;
    }

    public function invoke(): Engine
    {
        $engine = new Engine($this->context, $this->filesystemLoader);
        $engine->registerFunction1('asset', fn (string $fileName) => "{$this->url}/css/{$fileName}");

        return $engine;
    }
}
