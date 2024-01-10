<?php

declare(strict_types=1);

namespace Kaa\Component\Router\RoutingTree;

use Kaa\Component\Generator\PhpOnly;
use Kaa\Component\Router\Dto\RouteDto;
use Kaa\Component\Router\Exception\EmptyPathException;
use Kaa\Component\Router\Exception\PathAlreadyExistsException;

#[PhpOnly]
class RoutingTree
{
    /** @var TreeNode[] */
    private array $head;

    /** @var TreeNode[][] */
    private array $createdElements;

    private const PATH_VARIABLE_BEGGING_SYMBOL = '{';

    public function __construct()
    {
        $this->head = [];
        $this->createdElements = [];
    }

    /**
     * @return TreeNode[]
     */
    public function getHead(): array
    {
        return $this->head;
    }

    /**
     * @throws EmptyPathException|PathAlreadyExistsException
     */
    public function addElement(RouteDto $route): void
    {
        $path = $route->route;
        $handler = $route->name;
        $method = $route->method;
        // Разбиваем строку на массив
        $nodes = self::parse($path);
        if ($nodes === []) {
            throw new EmptyPathException('Path cannot be empty');
        }
        $keys = [];
        // Каждую переменную заменяем {} саму переменную кладём в массив keys
        $nodesCount = count($nodes);
        for ($i = 0; $i < $nodesCount; $i++) {
            if (str_contains($nodes[$i], self::PATH_VARIABLE_BEGGING_SYMBOL)) {
                $keys[$i] = substr($nodes[$i], 1, strlen($nodes[$i]) - 2);
                $nodes[$i] = '{}';
            }
        }
        // Проверка на наличии в head элемента определённого метода
        $this->changeRealisedPath($nodes, $method, $handler, $keys, $route);
        // Переменная содержащая путь к определённой ноде
        $prevKey = $method;
        for ($i = 0; $i < $nodesCount - 1; $i++) {
            $prevKey = $this->nodeAdding($i, $prevKey, $nodes);
        }

        $isVar = str_contains($nodes[$nodesCount - 1], self::PATH_VARIABLE_BEGGING_SYMBOL);
        $prom = new TreeNode($nodes[$nodesCount - 1], $handler, $keys, $route, $isVar);
        /** @var TreeNode $createdElement */
        $createdElement = $this->createdElements[$nodesCount - 1][$prevKey];
        $createdElement->addNext($prom);

        // {$prevKey}/{$nodes[count($nodes) - 1]} ключ по которому хранятся последние части каждого путя
        $this->createdElements[$nodesCount]["{$prevKey}/{$nodes[$nodesCount - 1]}"] = $prom;
    }

    /**
     * @return string[]
     */
    private static function parse(string $path): array
    {
        $parts = explode('/', trim($path, '/'));
        if ($parts[0] === '') {
            array_shift($parts);
        }

        return $parts;
    }

    /**
     * @param string[] $nodes
     * @param string[] $keys
     * @throws PathAlreadyExistsException
     */
    private function changeRealisedPath(
        array $nodes,
        string $method,
        string $handler,
        array $keys,
        RouteDto $route,
    ): void {
        $nodesCount = count($nodes);
        if (array_key_exists($method, $this->createdElements[0] ?? [])) {
            // Создаём полный путь с методом
            $existPath = implode('/', [$method, ...$nodes]);
            // Проверяем нет ли похожего путя в дереве
            if (array_key_exists($existPath, $this->createdElements[$nodesCount])) {
                /** @var TreeNode $realisedElement */
                $realisedElement = $this->createdElements[$nodesCount][$existPath];
                // Если похожий путь есть, но он ни к чему не ведёт - меняем
                if ($realisedElement->getHandler() === null) {
                    $realisedElement->setHandler($handler);
                    $realisedElement->setKeys($keys);
                    $realisedElement->setRoute($route);
                    $realisedElement->setVariable(str_contains($nodes[$nodesCount - 1], self::PATH_VARIABLE_BEGGING_SYMBOL));

                    return;
                }
                // Если есть путь с таким же именем - ничего не делаем
                if ($realisedElement->getHandler() === $handler) {
                    return;
                }
                // Если такой же путь, но с другим именем - выдаём ошибку
                throw new PathAlreadyExistsException(
                    "Path '{$route->route}' with different name already exists!",
                );
            }
            // Если такого метода нет - создаём и добавляем в head
        } else {
            $prom = new TreeNode($method);
            $this->head[] = $prom;
            $this->createdElements[0][$method] = $prom;
        }
    }

    /**
     * @param string[] $nodes
     */
    private function nodeAdding(int $index, string $prevKey, array $nodes): string
    {
        if (!array_key_exists("{$prevKey}/{$nodes[$index]}", $this->createdElements[$index + 1] ?? [])) {
            $prom = new TreeNode($nodes[$index], isVariable: str_contains($nodes[$index], '{'));
            /** @var TreeNode $realisedElement */
            $realisedElement = $this->createdElements[$index][$prevKey];
            $realisedElement->addNext($prom);
            $this->createdElements[$index + 1]["{$prevKey}/{$nodes[$index]}"] = $prom;
        }

        return "{$prevKey}/{$nodes[$index]}";
    }
}
