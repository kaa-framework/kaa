<?php

namespace Kaa\Component\Database\Attribute;

use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
enum GeneratedType: string
{
    case None = 'None';

    case AutoIncrement = 'AutoIncrement';
}
