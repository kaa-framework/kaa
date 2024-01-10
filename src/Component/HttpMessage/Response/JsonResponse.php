<?php

declare(strict_types=1);

namespace Kaa\Component\HttpMessage\Response;

use JsonEncoder;
use Kaa\Component\HttpMessage\Exception\JsonException;
use Kaa\Component\HttpMessage\HttpCode;

class JsonResponse extends Response
{
    /**
     * @param string|null $content Json string
     * @param array<string, string> $headers
     */
    public function __construct(?string $content = '', int $status = HttpCode::HTTP_OK, array $headers = [])
    {
        $headers[] = 'Content-Type: application/json';
        parent::__construct($content, $status, $headers);
    }

    /**
     * @param string[] $headers
     * @throws JsonException
     */
    public static function fromObject(object $data, int $status = HttpCode::HTTP_OK, array $headers = []): self
    {
        $json = JsonEncoder::encode($data);
        if ($json === '' && JsonEncoder::getLastError() !== '') {
            throw new JsonException(JsonEncoder::getLastError());
        }

        return new self($json, $status, $headers);
    }
}
