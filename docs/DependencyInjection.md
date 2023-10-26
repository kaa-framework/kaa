# DependencyInjection

Инъекция зависимостей – процесс предоставления внешней зависимости программному компоненту. 
Благодаря ей вы можете создавать более гибкие приложения с возможностью повторного использования классов. Здесь вы узнаете, как легко настроить и использовать 
её в вашем приложении. 

- [DependencyInjection](#dependencyinjection)
  - [Сервисы](#сервисы)
    - [ContainerGenerator](#containergenerator)
    - [Создание и регистрация сервиса](#создание-и-регистрация-сервиса)
  - [Инъекция зависимостей](#инъекция-зависимостей)
    - [Автоматическое связывание](#автоматическое-связывание)
  - [Использование фабрик для создания сервиса](#использование-фабрик-для-создания-сервиса)
    - [Статические фабрики](#статические-фабрики)
    - [Нестатические фабрики](#нестатические-фабрики)


## Сервисы
Обычно ваше приложение содержит большое количество объектов, которые выполняют конкретные задачи: работа с базой данных, отправка
сообщений и тд. Такие объекты мы называем **сервисами**, которые мы храним в **хранилище сервисов**, дабы инъекция зависимостей могла работать корректно.

Для начала стоит создать объект класса `ContainerGenerator()` и вызвать у него метод `generate()`:

```php
use Kaa/DependencyInjection/Builders/ContainerGenerator;

$container = (new ContainerGenerator())->generate($config);
```
Для лучшего понимания стоит разобраться что вообще представляет собой данный генератор.

### ContainerGenerator
Данный класс представляет из себя следующее:
```php
use Kaa\DependencyInjection\ContainerInterface;
use Kaa\CodeGen\CodeInstanceCreatorInterface;

class ContainerGenerator implements GeneratorInterface
{
    public function generate(array $config): ContainerInterface
    {
        // ...
    }

    public static function makeInstanceCreator(array $config): CodeInstanceCreatorInterface
    {
        // ... 
    }
}

```
Где **$config** – ассоциативный массив вида:
```php
$config = [
    "scanNamespaces" => [
        "App\Controller"
    ],
    "ignore" => [
        "App\Controller\AdminController"
    ],
    "export" => [
        "directory" => "src/generated",
        "namespace" => "App\Generated",
        "className" => "Container"
    ],
    "params" => [
        "param_name" => 123
    ]
];
```

**scanNamespaces** – список пространства имён, которые должны быть сканированы для генерации инъекций.

**ignore** – список пространства имён или классов, которые должны быть проигнорированы при сканировании для генерации инъекций.

**export** – список параметров для выходного класса
- **directory** – директория для сохранения сгенерированного кода.
- **namespce** – пространства имён выходного класса. 
- **className** – имя выходного класса. 

**params** – параметры, которые могут потребоваться для создания экземпляров сервисов.  

Метод `generate()` создаёт объект класса, реализующего интерфейс `ContainerInterface`. Это и есть необходимый нам контейнер для сервисов.

Метод `makeInstanceCreator` создаёт объект класса, реализующего интерфейс `CodeInstanceCreatorInterface`. Данный объект можно передать, например, в Router. 
Кроме того, у данного объекта имеется метод `create() => string`, возвращающий строку вида: `App\Generated\Container::get(Service::class)`.

### Создание и регистрация сервиса
Для регистрации сервиса необходимо использовать атрибут `Service`:
```php
// src/Service/MessageGenerator.php
namespace App\Service;

#[Service(name: 'FirstService', alias: 'first.service', tags: [new Tag("myTag", [])], singleton: false)]
class MessageWriter implements WriterInterface
{
    public function write(): string
    {
        $message = 'Hello, this is MessageGenerator service!';
        return $message;
    }
}
```
Таким образом мы создали наш первый сервис. Теперь давайте разберём подробнее атрибут `Service` и посмотрим какой отвечает за что параметр:
- **name** – Имя сервиса, если его не задать, оно будет совпадать с названием класса
- **alias** – Псевдоним, присвоенный сервису. По умолчанию отсутствует (присвоено null)
- **tags** – Массив объектов `Tag(string $name, mixed $data)`
- **singleton** – Параметр отвечает за то, будут ли создаваться новые экземпляры класса при запросе сервиса. По умолчанию параметр имеет значение `true`, это означает, что,
если экземпляр уже создан, то при новых запросах сервиса будет передаваться уже созданный экземпляр класса   

Таким образом в нашем контейнере теперь будет зарегистрирован сервис `FirstService`, далее мы рассмотрим как с ним работать. При запуске кодогенерации, будут просканированы необходимые пространства имён в поисках классов с аттрибутом `Service`, далее для них будет сгенерирован необходимый код.
## Инъекция зависимостей
Теперь, когда мы зарегистрировали сервис, неплохо бы разобраться зачем нам это вообще было нужно. Предположим в нашем приложении есть какой-либо контроллер, который использует `MessageWriter`:
```php
namespace App\Controller;

use Kaa\Router\Attribute\Post;
use App\Service\MessageWriter;

class OutputController
{
    private MessageWriter $service;

    public function __construct(MessageWriter $writer)
    {
        $this->service = $writer;
    }

    #[Post('/write', 'write')]
    public function out(MessageWriter $writer)
    {
        $message = $this->service->write();
        // ...
    }
}
```
Очевидно, что `OutputController` не знает ни о каком `MessageWriter`, соответственно ни коим образом сам не сможет создать экземпляр данного класса.
В этом как раз нам и поможет инъекция зависимостей, которая создаст нужный экземпляр с нужными параметрами и передаст его в `out()` при необходимости.

Но давайте вернёмся к нашему контейнеру и посмотрим что мы можем с ним делать. А можем мы получать объекты сервисов или их параметры.

`$Container::get(OutPutController::class)` – Вернёт объект класса `OutputController`

`$Container::get(ServiceInterface::class, 'service_name')` – Вернёт объект сервиса, реализующий интерфейс `ServiceInterface` и имеющий название `service_name`. В качестве `service_name` можно передать псевдоним.

`$Container::getParam('param_name')` – Вернёт параметр с заданным именем `param_name`. Параметр задаётся атрибутом [Autowire](#автоматическое-связывание), о нём ниже.

`$Container::getByTag(ServiceInterface::class, 'tag') => array` – Вернёт массив объектов, реализующие `ServiceInterface` и содержащие тег `tag`

> **⚠ WARNING**
>  Если эти методы ничего не найдут – бросится исключение!


### Автоматическое связывание
Теперь предположим, что наш сервис использует другой сервис и/или скалярный аргумент (строка, число и т.д.). 
В таком случае нужно будет связать их. В этом нам поможет атрибут `Autowire`:
```php
readonly class Autowire
{
    public function __construct(
        public ?string $service = null,
        public ?string $env = null,
        public ?string $param = null
    ) {
    }
}
```
Где:
- **service** – Имя сервиса или его псевдоним
- **env** – Имя переменной окружения
- **param** – Имя параметра, заданного в конфиге

Использовать это достаточно просто:
```php
namespace App\Service;

use Kaa\DependencyInjection\Attribute\Autowire;

class MessageGenerator
{
    public function __construct(
        #[Autowire(service: 'some_service')]
        private $service1,

        #[Autowire(param: 'kernel_debug')]
        bool $debugMode,

        #[Autowire(env: 'SOME_ENV_VAR')]
        string $senderName
    ) {
    }
    // ...
}
```
Таким образом, при создании объекта `MessageGenerator`, DI будет искать сервис с определённым указанным именем, а также параметр и переменную окружения. 

> **⚠ WARNING**
>  Если DI не найдёт нужные сервисы или переменные – бросится исключение!

## Использование фабрик для создания сервиса
Иногда вы хотите использовать фабрику (специальный объект для создания экземпляров класса), а не создавать экземпляр сервиса напрямую с помощью контейнера. 
Именно тогда вам придёт на помощь атрибут `Factory`:

```php
readonly class Factory
{
    public function __construct(
        public string $name,
        public string $method = 'generate',
        public bool $isStatic = false
    ) {
    }
}

```
Разберём что есть что:
- **name** – Имя сервиса фабрики
- **method** – Название метода, который будет вызван для создания сервиса
- **isStatic** – Если true, то `$factoryMethod` будет вызван статически у класса `$factoryService`

### Статические фабрики

На примере, работа со статической фабрикой будет выглядеть так:
```php
namespace App\Service;

#[Factory(StaticFactory::class, 'create', isStatic: true)]
class ServiceWithStaticFactory
{
    // ...
}
```
Таким образом мы зарегистрировали сервис, который будет создан с помощью статического метода `create()` фабрики `StaticFactory`.

### Нестатические фабрики
Если фабрика не является статической, то, для начала, необходимо её саму зарегистрировать как сервис, то есть:
```php
namespace App\Factory;

#[Service(name: 'MyFactory')]
class NonStaticFactory implements FactoryInterface
{
    public function generateService(): SpecialService
    {
        $service = new SpecialService();
        // ...
        return $service;
    }
}

```
> **⚠ WARNING**
>  KPHP выдаст ошибку компиляции, если использовать магический метод `__invoke`. Используйте своё название метода и обязательно указывайте его в атрибуте!

Далее уже можно использовать фабрику при описании сервиса:
```php
namespace App\Service;
use App\Factory\NonStaticFactory;

#[Factory(NonStaticFactory::class, 'generateService')]
class SpecialService
{
    // ...
}

```