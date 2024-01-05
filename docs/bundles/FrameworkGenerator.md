# FrameworkGenerator

### Содержание

* [Введение](#введение)
* [BundleGeneratorInterface](#Bundlegeneratorinterface)
* [FrameworkGenerator](#frameworkgenerator)
* [Пример приложения](#пример-приложения-на-kaa)

### Введение

`FrameworkGenerator` - сердце фреймворка.
Он сгенерирует весь код, который необходим для полноценной работы приложения.

### BundleGeneratorInterface

```php
interface BundleGeneratorInterface extends GeneratorInterface
{
    public function getRootConfigurationKey(): string;
    public function getConfiguration(): ?Symfony\Component\Config\Definition\NodeInterface;
    public function getPriority(): int;
    public function getConfigArray();
}
```

`BundleGeneratorInterface` используется для создания генераторов, которые можно легко подключить к приложению клиента.

В приложениях, использующих фреймворк конфигурация задаётся в `yaml`-файлах.
При запуске генерации кода, все `yaml`-файлы парсятся и сливаются в один `php`-массив.
После этого весь массив проверяется, и если в нём есть ключ или значение, обёрнутое в `%env(name)%`, то оно заменяется на значение из файла `env.json`.
Затем создаётся экземпляр каждого генератора и ему передаётся обработанный конфиг по ключу из `getRootConfigurationKey`.

### FrameworkGenerator

`FrameworkGenerator` - класс, который соединяет все генераторы вместе.
Если `FrameworkGenerator` видит в модулях класс, который реализует `NewInstanceGeneratorInterface`, то он добавляет его
в `SharedConfig` до запуска генераторов.

```php
// Это упрощённая реализация

use Symfony\Component\Config\Definition\Processor;

class FrameworkGenerator
{
    public function generate(string $pathToConfig, string $pathToGenerated, string $pathToEnvJson): void
    {
        $newInstanceGenerator = new DefaultNewInstanceGenerator();
        $generators = require_once $pathToConfig . '/Bundles.php';
        foreach ($generators as $generatorClass) {
            if (is_a($generatorClass, NewInstanceGenerator::class)) {
                $newInstanceGenerator = new $generatorClass();
            }
        }
        
        $generators = $this->sortByPriority($generators);
        
        $sharedConfig = new SharedConfig($pathToGenerated, $newInstanceGenerator);
        $config = $this->parseConfig($pathToConfig);
        
        $processor = new Processor();
        foreach ($generators as $generatorClass) {
            $generator = new $generatorClass();
            
            $generatorConfig = $processor->processConfiguration(
                $genenerator->getConfiguration(),
                $config[$generator->getRootConfigurationKey)()],
            );
           
            $generator->generate($sharedConfig, $config);
            
            $configArray = $generator->getConfigArray();
            $config = array_merge_recursive($config, $configArray);
        }
    }
}
```

### Пример приложения на `Kaa`

Структура проекта:

```
config/
------router.yaml
------services.yaml
------ ...
------Bundles.php
src/
generated/
generate.php
compoer.json
composer.lock
```

```php
<?php
// generate.php

(new FrameworkGenerator)->generate(__DIR__.'/config', __DIR__.'/generated', __DIR__.'/env.json');
```

```php
<?php
// config/Bundles.php

return [
    Kaa\Bundle\Router\RouterBundle::class,
    Kaa\Bundle\Validator\ValidatorBundle::class,
    Kaa\Bundle\Security\SecurityBundle::class,
    Kaa\Bundle\DependencyInjection\DependencyInjectionBundle::class,
    Kaa\Bundle\DependencyInjection\DependencyInjectionInstanceProvider::class,
];
```

```json
// composer.json

...

"autoload": {
"psr-4": {
"App\\": "src/",
"Kaa\\Generated\\": "generated/"
}
},

...
```
