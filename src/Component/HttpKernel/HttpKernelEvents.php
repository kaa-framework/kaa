<?php

declare(strict_types=1);

namespace Kaa\HttpKernel;

class HttpKernelEvents
{
    public const THROWABLE = 'http.kernel.throwable';

    public const REQUEST = 'http.kernel.request';

    public const FIND_ACTION = 'http.kernel.find.action';

    public const RESPONSE = 'http.kernel.response';
}
