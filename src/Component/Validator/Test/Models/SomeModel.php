<?php

declare(strict_types=1);

namespace Kaa\Component\Validator\Test\Models;

use Kaa\Component\Validator\Assert;

class SomeModel
{
    #[Assert\Blank]
    public string $text = '12345';
}
