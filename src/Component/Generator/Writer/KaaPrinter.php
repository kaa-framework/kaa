<?php

declare(strict_types=1);

namespace Kaa\Component\Generator\Writer;

use Kaa\Component\Generator\PhpOnly;
use Nette\PhpGenerator\Closure;
use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Printer;
use Nette\PhpGenerator\PromotedParameter;

#[PhpOnly]
class KaaPrinter extends Printer
{
    protected function printParameters(Closure|GlobalFunction|Method $function, int $column = 0): string
    {
        $special = false;
        foreach ($function->getParameters() as $param) {
            $param->validate();
            $special = $special || $param instanceof PromotedParameter || $param->getAttributes() || $param->getComment();
        }

        if (!$special || ($this->singleParameterOnOneLine && count($function->getParameters()) === 1)) {
            $line = $this->formatParameters($function);
            if (!str_contains($line, "\n") && strlen($line) + $column <= $this->wrapLength) {
                return $line;
            }
        }

        return $this->formatParameters($function);
    }

    private function formatParameters(Closure|GlobalFunction|Method $function): string
    {
        $params = $function->getParameters();
        $res = '';

        foreach ($params as $param) {
            $variadic = $function->isVariadic() && $param === end($params);
            $attrs = $this->printAttributes($param->getAttributes(), inline: true);
            $res .=
                $this->printDocComment($param)
                . $attrs
                . ($param instanceof PromotedParameter
                    ? ($param->getVisibility() ?: 'public') . ($param->isReadOnly() && $param->getType() ? ' readonly' : '') . ' '
                    : '')
                . ltrim($this->printType($param->getType(), $param->isNullable()) . ' ')
                . ($param->isReference() ? '&' : '')
                . ($variadic ? '...' : '')
                . '$' . $param->getName()
                . ($param->hasDefaultValue() && !$variadic ? ' = ' . $this->dump($param->getDefaultValue()) : '')
                . ', ';
        }

        return '(' . substr($res, 0, -2) . ')';
    }
}
