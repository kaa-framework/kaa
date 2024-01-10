# Generator

### Содержание

* [Введение](#введение)
* [GeneratorInterface](#generatorinterface)

### Введение

Kaa является компилируемым фреймворком.
Из-за этого многие задачи, которые обычные фреймворки решают с помощью рефлексии, в Kaa приходится решать генерацией
кода.
Например: роутинг, инъекция зависимостей, валидация моделей.

Для удобного создания модулей, использующих генерацию кода, и подключения их к пользовательским приложениям, все модули
в `Kaa` реализуют `Kaa\GeneratorContract\GeneratorInterface`.

### GeneratorInterface

```php
interface GeneratorInterface
{
    public function generate(SharedConfig $sharedConfig, array $config): void;
}
```

* `mixed[] $config` - массив с пользовательской конфигурацией генератора
* `SharedConfig $sharedConfig` - общая конфигурация для всех генераторов

```php
readonly class SharedConfig
{
    public function __construct(
        public string $exportDirectory,
        public NewInstanceGeneratorInterface $newInstanceGenerator = new DefaultNewInstanceGenerator(),
    )
}
```

* `string $exportDirectory` - директория, в которую сохранять сгенерированные классы.
  autoload composer'а должен указывать для этой директории `namespace` `Kaa\Generated`
* `NewInstanceGeneratorInterface $newInstanceGenerator` - объект, который позволяет создать код для получения сервиса по
  имени класса или алиасу

Дефолтный NewInstanceGenerator просто возвращает вызов конструктора без параметров, но при использовании модуля для
инъекции зависимостей в `$newInstanceGenerator` будет очень полезный класс.

```php
final class DefaultNewInstanceGenerator implements NewInstanceGeneratorInterface
{
    public function generate(string $className): string
    {
        return "new $className()";
    }
}
```

Например, простой генератор для роутинга мог бы вызываться так:

```php

$sharedConfig = new SharedConfig('./generated');
$routerConfig = [
    '/project' => 'App\Controller\ProjectController::getProjects',
    '/user' => 'App\Controller\ProjectController::getProjects',
]
$routerGenerator = new RouterGenerator($sharedConfig, $routerConfig);
```
