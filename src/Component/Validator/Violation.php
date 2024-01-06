<?php

declare(strict_types=1);

namespace Kaa\Component\Validator;

class Violation
{
    private string $className;

    private string $propertyName;

    private string $message;

    public function __construct(string $className, string $propertyName, string $message)
    {
        $this->className = $className;
        $this->propertyName = $propertyName;
        $this->message = $message;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
