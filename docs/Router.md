# Router компонент


Когда ваше приложение получает запрос, оно вызывает действие контроллера для генерации ответа.

Конфигурация маршрутизации определяет, какое действие выполнять для каждого входящего URL-адреса.


## Содержание

- [Router компонент](#router-компонент)
  - [Содержание](#содержание)
  - [RouterGenerator](#routergenerator)
    - [Конфигурация](#конфигурация)
  - [Создание путей](#создание-путей)
    - [Attribute creating](#attribute-creating)
    - [Маршрутизация для класса](#маршрутизация-для-класса)
    - [Поддерживаемые методы](#поддерживаемые-методы)
  - [Переменные](#переменные)
    - [Пример](#пример)
  - [Middleware](#middleware)




## RouterGenerator
Для создания маршрутизации необходимо создать экземпляр класса ```RoutingGenerator``` который реализует ```GeneratorInterface```.
У этого гласса есть метод ```generate(array $config)``` который генерирует класс, реализующий ```RouterInterface``` с методом ```findAction(Request $request): callable```.

Пример:
```php
class RoutingGenerator implements GeneratorInterface{
    public function generate(array $config): void
    {
        ...
    }
}
```
Это ```RouterInterface```:
```php
interface RouterInterface{
    public function findAction(Request $request): callable;
}
```
```callable``` – функция вида ```(Request) => Responce```


### Конфигурация
**$config** ассоциативный массив вида:
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
        "className" => "Router"
    ],
    "prefixes" => [
        "Kaa\SampleProject\Controller\BlogApiController" => "/api/"
    ],
    "instanceCreator" => App\IC\InstanceCreator::create(...)
];
```

**scanNamespaces** – список пространства имён, которые должны быть сканированы для генерации маршрутов.

**ignore** – список пространства имён или классов, которые должны быть проигнорированы при сканировании для генерации маршрутов.

**export** – список параметров для выходного класса
- **directory** – директория для сохранения сгенерированного кода.
- **namespce** – пространства имён выходного класса. 
- **className** – имя выходного класса.   

**prefixes** – список с указанием префиксов у контроллеров, вида ***контроллер*** => ***префикс***

**instanceCreator** – метод, создающий новый экземпляры классов. Это может быть DI или что-либо ещё.


## Создание путей
### Attribute creating
Атрибуты PHP позволяют определять маршруты рядом с кодом контроллеров, связанных с этими маршрутами. Атрибуты являются встроенными в PHP 8 и более поздних версиях, поэтому вы можете использовать их сразу.

Предположим, вы хотите определить маршрут для `/healthcheck` URL. Вот небольшой пример:
```php
<?php

namespace Kaa\SampleProject\Controller;

use Kaa\HttpKernel\Request;
use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\Router\Attribute\Get;

class HealthCheckController
{
    #[Get('/healthcheck', 'healthcheck')]
    public function healthcheck(): ResponseInterface
    {
        // ...
    }
}
```
Эта конфигурация определяет маршрут под названием `healthcheck` который соответствует запросу `GET` для `/blog` URL.
```Router``` создаёт объект класса ```HealthCheckController``` и возвращает ссылку на его метод ```healthcheck```.


Вы можете добавить несколько маршрутов для метода класса контроллера:

```php
<?php

namespace Kaa\SampleProject\Controller;

use Kaa\HttpKernel\Request;
use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\Router\Attribute\Get;
use Kaa\Router\Attribute\Post;

class HealthCheckController
{
    #[Get('/healthcheck', 'healthcheck')]
    #[Post('/posthealthcheck', 'post_healthcheck')]
    public function healthcheck(): ResponseInterface
    {
        // ...
    }
}
```

###  Маршрутизация для класса
Вы также можете добавить `Route` для класса, если вы хотите добавить префикс для url:
```php
<?php

namespace Kaa\SampleProject\Controller;

use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\Router\Attribute\Get;
use Kaa\Router\Attribute\Route;

#[Route('/class')]
class HealthCheckController
{
    #[Get('/healthcheck', 'healthcheck')]
    public function healthcheck(): ResponseInterface
    {
        // ...
    }
}
```

Так мы определили поведение для `GET/class/healthcheck` URL.
> **⚠ WARNING**  
> Если вы используете `Route` для добавления префикса, вы **не должны** указывать ему никакие HTTP-методы 

> **⚠ WARNING**  
> Если вы не укажете имя роута, оно будет сгенерировано автоматически


### Поддерживаемые методы
Список поддерживаемых аттрибутов:

| Аттрибут | Метод  |
|-----------|---------|
| `Route`   | Нет     |
| `Get`     | GET     |
| `Post`    | POST    |
| `Head`    | HEAD    | 
| `Put`     | PUT     |
| `Patch`   | PATCH   |
| `Delete`  | DELETE  |



## Переменные

В большинстве случаев некоторые части вашего пути являются переменными, вы можете определить эти пути, обернув
изменяемые части `{}` Например:

### Пример
```php
namespace Kaa\SampleProject\Controller;

use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\Router\Attribute\Get;

class HealthCheckController
{
    #[Get('/healthcheck/{id}', 'healthcheck')]
    public function healthcheck(int $id): ResponseInterface
    {
        // ...
    }
}
```
Теперь, если наше приложение получит запрос методом GET с путём ```healthcheck/12```, ```findAction()``` вызовет ```healthcheck(12)``` (параметр **$id** = 12).


## Middleware
Middleware предоставить удобный механизм проверки и фильтрации HTTP-запросов, поступающих в ваше приложение.
Вы также можете использовать промежуточное программное обеспечение, добавляя атрибуты для методов класса контроллера.

Для начала, вы должны создать класс middleware, реализующий ```BeforeMiddlewareInterface``` или ```AfterMiddlewareInterface```, для начала:
```php
namespace App\Middlewares;

class AuthMiddleware implements BeforeMiddlewareInterface {
    public function handle(ReflectionClass $class, ReflectionMethod $method, VariableBag $bag) : ResponceInterface
    {
        // ...
    }
}
```
А теперь напишем это:
```php
namespace Kaa\SampleProject\Controller;

use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\Router\Attribute\Get;
use App\Middlewares\AuthMiddleware;

class HealthCheckController
{
    #[Get('/healthcheck/{id}', 'healthcheck')]
    #[AuthMiddleware]
    public function healthcheck(int $id): ResponseInterface
    {
        // ...
    }
}

```
Как вы могли предсказатьь, ```findAction()``` вызовет ```AuthMiddleware``` до вызова ```healthcheck()```, если бы мы использовали ```AfterMiddlewareInterface```
для ```AuthMiddleware``` то он бы вызвался после ```healthcheck()```.

Кроме того, роутер также может определять атрибуты, расположенные рядом с параметрами метода. **Важно**: атрибуты должны быть унаследованы от ```BeforeMiddlewareInterface```
или ```AfterMiddlewareInterface```.

Предположим, у вас есть ```CheckDiniedMiddleware```, проверяющий, что параметр не находится среди запрещённых. Предположим, есть такой код:

```php
namespace Kaa\SampleProject\Controller;

use Kaa\HttpKernel\Response\ResponseInterface;
use Kaa\Router\Attribute\Get;
use App\Middlewares\CheckDiniedMiddleware;

class HealthCheckController
{
    #[Get('/healthcheck/{id}', 'healthcheck')]
    public function healthcheck(
        #[CheckDiniedMiddleware] int $id
    ): ResponseInterface
    {
        // ...
    }
}

```
Таким образом, роутер определит следующее поведение: после получения `Get` запроса по пути `/healthcheck/{id}`, до запуска `healthcheck()`, будет запущен `CheckDiniedMiddleware`, 
уже после которого будет запущен необходимый метод.

Это позволяет удобно и красиво настроить различные валидации.
