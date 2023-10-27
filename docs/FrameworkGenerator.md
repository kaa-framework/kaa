# FrameworkGenerator


### Введение
`FrameworkGenerator` - сердце фреймворка.
Он сгенерирует весь код, который необходим для полноценной работы приложения.

### ModuleGeneratorInterface

```php
interface ModuleGeneratorInterface extends GeneratorInterface
{
    public function getRootConfigurationKey(): string;
    public function getConfiguration(): Symfony\Component\Config\Definition\NodeInterface;
    public function getPriority(): int;
}
```

`ModuleGeneratorInterface` используется для создания генераторов, которые можно легко подключить к приложению клиента.

В приложениях, использующих фреймворк конфигурация задаётся в `yaml`-файлах.
При запуске генерации кода, все `yaml`-файлы парсятся и сливаются в один `php`-массив.
Затем создаётся экземпляр каждого генератора и ему передаётся обработанный конфиг по ключу из `getRootConfigurationKey`.

### FrameworkGenerator

`FrameworkGenerator` - класс, который соединяет все генераторы вместе.
Если `FrameworkGenerator` видит в модулях класс, который реализует `NewInstanceGeneratorInterface`, то он добавляет его в `SharedConfig` до запуска генераторов.

```php
// Это упрощённая реализация

use Symfony\Component\Config\Definition\Processor;

class FrameworkGenerator
{
    public function generate(string $pathToConfig, string $pathToGenerated): void
    {
        $newInstanceGenerator = new DefaultNewInstanceGenerator();
        $generators = require_once $pathToConfig . '/modules.php';
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
------modules.php
src/
generated/
generate.php
compoer.json
composer.lock
```

```php
<?php
// generate.php

(new FrameworkGenerator)->generate(__DIR__.'/config', __DIR__.'/generated');
```

```php
<?php
// config/modules.php

return [
    Kaa\Module\Router\RouterModule::class,
    Kaa\Module\Validator\ValidatorModule::class,
    Kaa\Module\Security\SecurityModule::class,
    Kaa\Module\DependencyInjection\DependencyInjectionModule::class,
    Kaa\Module\DependencyInjection\DependencyInjectionInstanceProvider::class,
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
