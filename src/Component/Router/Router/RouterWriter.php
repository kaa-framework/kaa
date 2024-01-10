<?php

declare(strict_types=1);

namespace Kaa\Component\Router\Router;

use Kaa\Component\Generator\Exception\WriterException;
use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Generator\SharedConfig;
use Kaa\Component\Generator\Writer\ClassWriter;
use Kaa\Component\Generator\Writer\Parameter;
use Kaa\Component\Generator\Writer\TwigFactory;
use Kaa\Component\Generator\Writer\Visibility;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\Router\Router\RoutingTree\RoutingTree;
use Kaa\Component\Router\RouterInterface;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
final class RouterWriter
{
    private ClassWriter $classWriter;
    private Twig\Environment $twig;

    public function __construct(
        private readonly SharedConfig $config,
        private readonly RoutingTree $tree,
    ) {
        $this->classWriter = new ClassWriter(
            namespaceName: 'Router',
            className: 'Router',
            implements: [RouterInterface::class],
        );

        $this->twig = TwigFactory::create(__DIR__ . '/../templates');
    }

    /**
     * @throws LoaderError|WriterException|RuntimeError|SyntaxError
     */
    public function write(): void
    {
        $code = $this->generateCode();

        $this->classWriter->addMethod(
            visibility: Visibility::Public,
            name: 'findAction',
            returnType: 'callable|null',
            code: $code,
            parameters: [
                new Parameter(type: Request::class, name: 'request'),
            ],
            comment: '@return (callable(\Kaa\Component\HttpMessage\Request): \Kaa\Component\HttpMessage\Response\Response)|null',
        );

        $this->classWriter->writeFile($this->config->exportDirectory);
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function generateCode(): string
    {
        $code = [];
        $indexes = [];
        $parents = [];
        $code[] = $this->twig->render('setupcode.php.twig');
        foreach ($this->tree->getHead() as $headItem) {
            $code[] = "if (\$method === '{$headItem->getData()}'){\n";
            // Глубина вложенности
            $depth = 0;
            // Предыдущие элементы для определённой глубины
            $parents[$depth] = $headItem->getNext();
            // Индекс первого предыдущего элемента для которого не построен блок if-else
            $indexes[$depth] = 0;

            while (true) {
                // Проверяем что ещё не все блоки для родителей готовы
                if ($indexes[$depth] < count($parents[$depth])) {
                    $current = $parents[$depth][$indexes[$depth]];
                    $code[] = $this->twig->render(
                        'while_body.php.twig',
                        [
                            'current' => $current,
                            'count' => $depth + 1,
                        ],
                    );
                    // Если у текущей ноды есть следующие
                    if ($current->getNext() !== []) {
                        $code[] = $this->twig->render(
                            'while_body_second.php.twig',
                            [
                                'indexes' => $indexes,
                                'depth' => $depth,
                                'route' => $parents[$depth][$indexes[$depth]]->getData(),
                            ],
                        );
                        $indexes[$depth]++;
                        $depth++;
                        $indexes[$depth] = 0;
                        $parents[$depth] = $parents[$depth - 1][$indexes[$depth - 1] - 1]->getNext();
                        // Если следующих нет
                    } else {
                        $indexes[$depth]++;
                    }
                    // Когда все блоки для родительских элементов текущей глубины готовы
                } else {
                    $code[] = "}\n";
                    // Заканчиваем как только это произошло для нод из head
                    if ($depth === 0) {
                        break;
                    }
                    // Поднимаемся на уровень выше в ином случае
                    unset($parents[$depth]);
                    $depth--;
                }
            }
        }

        $code[] = "return null;\n";

        return implode("\n", $code);
    }
}
