<?php

namespace Kaa\Bundle\KTemplate;

use Kaa\Component\HttpMessage\HttpCode;
use Kaa\Component\HttpMessage\Response\Response;
use Kaa\Component\RequestMapperDecorator\MapRouteParameter;

class KTemplateController
{
    private const MIME_TYPES = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',

        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    ];

    private string $path;

    public function getFile(
        #[MapRouteParameter]
        string $fileName,
        #[MapRouteParameter]
        string $fileType
    ): Response {
        $fileName = str_replace('___', '.', $fileName);
        $fileName = str_replace('---', '/', $fileName);
        $filePath = $this->path . '/' . $fileName;
        if (!file_exists($filePath)) {
            return new Response(null, HttpCode::HTTP_NOT_FOUND);
        }

        $content = file_get_contents($filePath);
        $statusCode = HttpCode::HTTP_OK;
        if ($content === false) {
            return new Response(null, HttpCode::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $content,
            $statusCode,
            ['Content-type: ' . self::MIME_TYPES[$fileType]]
        );
    }

    public function __construct(
        string $path
    ) {
        $this->path = $path;
    }
}
