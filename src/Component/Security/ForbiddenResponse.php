<?php

namespace Kaa\Component\Security;

use Kaa\Component\HttpMessage\HttpCode;
use Kaa\Component\HttpMessage\Response\Response;

class ForbiddenResponse extends Response
{
    public function __construct()
    {
        parent::__construct('', HttpCode::HTTP_FORBIDDEN);
    }
}
