<?php

namespace Kaa\Component\Router\Decorator;

class Variables
{
    /** @var array<string, string> */
    private array $variables = [];

    private string $actualReturnValueName = 'kaaRetVal';

    public function addVariable(string $type, string $name): void
    {
        $this->variables[$name] = $type;
    }

    public function getControllerReturnValueName(): string
    {
        return 'kaaRetVal';
    }

    public function hasSame(string $type, string $name): bool
    {
        return array_key_exists($name, $this->variables)
            && $this->variables[$name] === $type;
    }

    public function getLastByType(string $type): ?string
    {
        $name = null;

        foreach ($this->variables as $varName => $varType) {
            if ($varType === $type || is_a($varType, $type, true)) {
                $name = $varName;
            }
        }

        return $name;
    }

    public function getActualReturnValueName(): string
    {
        return $this->actualReturnValueName;
    }

    public function setActualReturnValueName(string $actualReturnValueName): self
    {
        $this->actualReturnValueName = $actualReturnValueName;

        return $this;
    }
}
