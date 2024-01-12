<?php

namespace Kaa\Bundle\Security\Writer;

use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Generator\Writer\ClassWriter;
use Kaa\Component\Generator\Writer\Parameter;
use Kaa\Component\Generator\Writer\TwigFactory;
use Kaa\Component\Generator\Writer\Visibility;
use Kaa\Component\HttpKernel\Event\RequestEvent;
use Kaa\Component\Security\SecurityInterface;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
readonly class ListenerWriter
{
    private ClassWriter $classWriter;
    private Twig\Environment $twig;

    public function __construct(
        private SharedConfig $config,
    ) {
        $this->classWriter = new ClassWriter(
            namespaceName: 'Security',
            className: 'RequestListener',
        );

        $this->twig = TwigFactory::create(__DIR__ . '/../templates');
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError|WriterException
     */
    public function write(): void
    {
        $code = $this->twig->render('invoke.php.twig', [
            'service' => $this->config->newInstanceGenerator->generate(SecurityInterface::class, SecurityInterface::class),
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'invoke',
            returnType: 'void',
            code: $code,
            parameters: [
                new Parameter(type: RequestEvent::class, name: 'event'),
            ],
        );

        $this->classWriter->writeFile($this->config->exportDirectory);
    }
}
