<?php

declare(strict_types=1);

namespace Kaa\Bundle\EventDispatcher\Writer;

use Kaa\Bundle\EventDispatcher\Exception\WriteFileException;
use Kaa\Bundle\EventDispatcher\ListenerMethodName;
use Kaa\Component\EventDispatcher\EventDispatcherInterface;
use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\GeneratorContract\SharedConfig;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
readonly class BootstrapWriter
{
    private Twig\Environment $twig;

    /**
     * @param mixed[] $listeners
     */
    public function __construct(
        private SharedConfig $config,
        private array $listeners,
    ) {
        $this->twig = $this->createTwig();
    }

    private function createTwig(): Twig\Environment
    {
        $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');

        return new Twig\Environment($loader);
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError|WriteFileException
     */
    public function write(): void
    {
        $fileName = $this->config->exportDirectory . '/bootstrap.php';
        if (!file_exists($fileName)) {
            $file = fopen($fileName, 'wb');
            fwrite($file, "<?php \n");
        } else {
            $file = fopen($fileName, 'ab');
        }

        if ($file === false) {
            throw new WriteFileException("Could not write to file {$fileName}");
        }

        fwrite($file, "\$kaaEventDispatcherListener = new \Kaa\Generated\EventDispatcher\Listener();\n");

        try {
            foreach ($this->listeners as $listener) {
                $code = $this->twig->render('add_listener.php.twig', [
                    'dispatcher' => $this->config->newInstanceGenerator->generate(
                        'kernel.dispatcher.' . $listener['dispatcher'],
                        EventDispatcherInterface::class
                    ),
                    'method' => ListenerMethodName::get($listener),
                    'event' => $listener['event'],
                    'priority' => $listener['priority'],
                ]);

                fwrite($file, "\n" . $code . "\n");
            }
        } finally {
            fclose($file);
        }
    }
}
