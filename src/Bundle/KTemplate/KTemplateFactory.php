<?php

namespace Kaa\Bundle\KTemplate;

use KTemplate\Context;
use KTemplate\Engine;
use KTemplate\FilesystemLoader;

class KTemplateFactory
{
    private Context $context;
    private string $url;
    private string $templatePath;

    public function __construct(
        Context $context,
        string $url,
        string $templatePath
    ) {
        $this->context = $context;
        $this->url = $url;
        $this->templatePath = $templatePath;
    }

    public function invoke(): Engine
    {
        $engine = new Engine($this->context, new FilesystemLoader([$this->templatePath]));
        $engine->registerFunction2('asset', function (mixed $fName, mixed $fType) {
            $fileName = (string) $fName;
            $fileType = (string) $fType;
            $fileName = str_replace('.', '___', $fileName);
            $fileName = str_replace('/', '---', $fileName);

            return "{$this->url}/kaa_get_file/{$fileName}/{$fileType}";
        });

        return $engine;
    }
}
