<?php

namespace Kaa\Component\Security\Writer;

use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Generator\Writer\ClassWriter;
use Kaa\Component\Generator\Writer\Parameter;
use Kaa\Component\Generator\Writer\TwigFactory;
use Kaa\Component\Generator\Writer\Visibility;
use Kaa\Component\Security\AbstractSecurity;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
readonly class SecurityWriter
{
    private ClassWriter $classWriter;
    private Twig\Environment $twig;

    /**
     * @param mixed[] $voters
     * @param mixed[] $firewalls
     * @param mixed[] $accessControl
     */
    public function __construct(
        private SharedConfig $config,
        private array $voters,
        private array $firewalls,
        private array $accessControl,
    ) {
        $this->classWriter = new ClassWriter(
            namespaceName: 'Security',
            className: 'Security',
            extends: AbstractSecurity::class,
        );

        $this->twig = TwigFactory::create(__DIR__ . '/../templates');
    }

    /**
     * @throws SyntaxError|WriterException|RuntimeError|LoaderError
     */
    public function write(): void
    {
        $this->addGetAuthenticators();
        $this->addGetAccessControl();
        $this->addIsGranted();

        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addGetAuthenticators(): void
    {
        $code = $this->twig->render('get_authenticators.php.twig', [
            'instanceProvider' => $this->config->newInstanceGenerator,
            'firewalls' => $this->firewalls,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Protected,
            name: 'getAuthenticators',
            returnType: 'array',
            code: $code,
            parameters: [new Parameter(type: 'string', name: 'route')],
            comment: '@return \Kaa\Component\Security\AuthenticatorInterface[]',
        );
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addGetAccessControl(): void
    {
        $code = $this->twig->render('get_access_control.php.twig', [
            'accessControl' => var_export($this->accessControl, true),
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Protected,
            name: 'getAccessControl',
            returnType: 'array',
            code: $code,
            comment: '@return array<string, string[]>',
        );
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addIsGranted(): void
    {
        $code = $this->twig->render('is_granted.php.twig', [
            'instanceProvider' => $this->config->newInstanceGenerator,
            'voters' => $this->voters,
        ]);

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'isGranted',
            returnType: 'bool',
            code: $code,
            parameters: [
                new Parameter(type: 'string', name: 'attribute'),
                new Parameter(type: 'array', name: 'subject', defaultValue: '[]'),
            ],
            comment: '@param string[] $subject',
        );
    }
}
