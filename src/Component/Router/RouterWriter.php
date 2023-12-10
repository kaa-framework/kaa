<?php

declare(strict_types=1);

namespace Kaa\Component\Router;

use Kaa\Component\GeneratorContract\PhpOnly;
use Kaa\Component\GeneratorContract\SharedConfig;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\Router\Exception\RouterGeneratorException;
use Kaa\Component\Router\RoutingTree\RoutingTree;
use Nette\PhpGenerator\ClassLike;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[PhpOnly]
final class RouterWriter
{
    private PhpFile $file;
    private ClassType $class;
    private Twig\Environment $twig;

    public function __construct(
        private SharedConfig $config,
        private RoutingTree $tree,
    ) {
        $this->file = new PhpFile();
        $this->file->setStrictTypes();

        $namespace = $this->file->addNamespace('Kaa\\Generated\\Router');
        $this->class = $namespace->addClass('Router')->addImplement(RouterInterface::class);

        $this->twig = $this->createTwig();
    }

    private function createTwig(): Twig\Environment
    {
        $loader = new Twig\Loader\FilesystemLoader(__DIR__ . '/templates');

        return new Twig\Environment($loader);
    }

    /**
     * @throws RouterGeneratorException
     */
    public function write(): void
    {
        $this->addFindMethod();
        $this->writeFile();
    }

    /**
     * @throws RuntimeError|SyntaxError|LoaderError
     */
    private function addFindMethod(): void
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

        $code[] = "return [];\n";
        $method = $this->addMethod(
            implode("\n", $code),
            ClassLike::VisibilityPublic,
        );
        $method->addParameter('request')->setType(Request::class);
    }

    private function addMethod(
        string $code,
        string $visibility = ClassLike::VisibilityPrivate,
    ): Method {
        $method = $this->class->addMethod('findAction');
        $method->setReturnType('callable');
        $method->setStatic();
        $method->setVisibility($visibility);
        $method->setBody($code);

        return $method;
    }

    /**
     * @throws RouterGeneratorException
     */
    private function writeFile(): void
    {
        $directory = $this->config->exportDirectory . '/Router';
        if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
            throw new RouterGeneratorException("Directory {$directory} was not created");
        }
        file_put_contents(
            $directory . '/Router.php',
            (new PsrPrinter())->printFile($this->file),
        );
    }
}
