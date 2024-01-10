<?php

declare(strict_types=1);

namespace Kaa\Bundle\EventDispatcher\Writer;

use Kaa\Bundle\EventDispatcher\ListenerMethodName;
use Kaa\Component\EventDispatcher\EventDispatcherInterface;
use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Generator\Writer\TwigFactory;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
readonly class ListenerBootstrapWriter
{
    private Twig\Environment $twig;

    /**
     * @param mixed[] $listeners
     */
    public function __construct(
        private SharedConfig $config,
        private array $listeners,
    ) {
        $this->twig = TwigFactory::create(__DIR__ . '/../templates');
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError|WriterException
     */
    public function write(): void
    {
        $this->config->bootstrapWriter->append(
            "\$kaaEventDispatcherListener = new \Kaa\Generated\EventDispatcher\Listener();\n"
        );

        foreach ($this->listeners as $listener) {
            $code = $this->twig->render('add_listener.php.twig', [
                'dispatcher' => $this->config->newInstanceGenerator->generate(
                    'kernel.dispatcher.' . $listener['dispatcher'],
                    EventDispatcherInterface::class
                ),
                'method' => ListenerMethodName::name($listener),
                'event' => $listener['event'],
                'priority' => $listener['priority'],
            ]);

            $this->config->bootstrapWriter->append($code . "\n");
        }
    }
}
