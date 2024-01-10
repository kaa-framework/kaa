<?php

namespace Kaa\Component\Generator\Writer;

use Kaa\Component\Generator\PhpOnly;
use Twig;

#[PhpOnly]
final class TwigFactory
{
    /**
     * @param Twig\TwigFilter[] $twigFilters
     */
    public static function create(string $templateDirectory, array $twigFilters = []): Twig\Environment
    {
        $loader = new Twig\Loader\FilesystemLoader($templateDirectory);
        $twig = new Twig\Environment($loader, [
            'autoescape' => false,
        ]);

        foreach ($twigFilters as $filter) {
            $twig->addFilter($filter);
        }

        return $twig;
    }
}
