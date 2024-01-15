<?php

namespace Kaa\Component\Database\Attribute;

use Attribute;
use Kaa\Component\Database\Exception\DatabaseGeneratorException;
use Kaa\Component\Generator\PhpOnly;

#[
    PhpOnly,
    Attribute(Attribute::TARGET_PROPERTY),
]
final readonly class Column
{
    /**
     * @throws DatabaseGeneratorException
     */
    public function __construct(
        public ?string $name = null,
        public Type $type = Type::Primitive,
        public bool $nullable = false,
    ) {
        if ($this->type === Type::SimpleArray && $this->nullable) {
            throw new DatabaseGeneratorException('SIMPLE_ARRAY fields must not be nullable');
        }
    }
}
