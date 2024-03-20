<?php

namespace Kaa\Bundle\KTemplate;

use Kaa\Component\DependencyInjection\Attribute\Autowire;
use Kaa\Component\HttpMessage\HttpCode;
use Kaa\Component\HttpMessage\Response\Response;
use Kaa\Component\RequestMapperDecorator\MapRouteParameter;
use Kaa\Component\Router\Attribute\Route;

#[Route('/kaa/public')]
class KTemplateController
{
    private string $path;

    public function __construct(
        #[Autowire(parameter: 'kaa.ktemplate.path')]
        string $path,
    ) {
        $this->path = $path;
    }

    public function getCss(
        #[MapRouteParameter]
        string $fileName,
    ): Response {
        $content = file_get_contents($this->path . $fileName);
        $statusCode = HttpCode::HTTP_OK;
        if ($content === false) {
            $content = null;
            $statusCode = HttpCode::HTTP_NOT_FOUND;
        }

        return new Response(
            content: $content,
            status: $statusCode,
            headers: ['Content-type: text/css'],
        );
    }
}
