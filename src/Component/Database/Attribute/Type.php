<?php

namespace Kaa\Component\Database\Attribute;

use Kaa\Component\Database\Hydrator\CastFieldHydrator;
use Kaa\Component\Database\Hydrator\DateTimeImmutableFieldHydrator;
use Kaa\Component\Database\Hydrator\FieldHydratorInterface;
use Kaa\Component\Database\Hydrator\SimpleArrayFieldHydrator;
use Kaa\Component\Generator\PhpOnly;

#[PhpOnly]
enum Type: string
{
    case Primitive = 'Primitive';

    case DateTimeImmutable = 'DateTimeImmutable';

    case SimpleArray = 'SimpleArray';

    public function getHydrator(): FieldHydratorInterface
    {
        return match ($this) {
            self::Primitive => new CastFieldHydrator(),
            self::SimpleArray => new SimpleArrayFieldHydrator(),
            self::DateTimeImmutable => new DateTimeImmutableFieldHydrator(),
        };
    }
}
