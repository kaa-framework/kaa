<?php

declare(strict_types=1);

namespace Kaa\Component\Validator;

interface ValidatorInterface
{
    /**
     * @return Violation[]
     */
    public function validate(object $model): array;
}
