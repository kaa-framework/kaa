<?php

namespace Kaa\Component\RequestMapperDecorator\Exception;

use Exception;
use Kaa\Component\Validator\Violation;

class ValidationException extends Exception
{
    /** @var Violation[] */
    private array $violationList;

    /**
     * @param Violation[] $violationList
     */
    public function __construct(array $violationList)
    {
        parent::__construct();

        $this->violationList = $violationList;
    }

    /**
     * @return Violation[]
     */
    public function getViolationList(): array
    {
        return $this->violationList;
    }
}
