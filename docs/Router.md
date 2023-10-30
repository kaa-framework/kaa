# Router

### Содержание

* [Введение](#введение)
* [Конфигурация роутера](#конфигурация-роутера)
* [Настройка маршрутизации для класса](#настройка-маршрутизации-для-класса)
* [Поддерживаемые методы](#поддерживаемые-методы)
* [Переменные пути](#переменные-пути)

### Введение

`Router` по объекту `Request` определяет, какой метод надо вызвать, чтобы его обработать.

### Использование генератора роутера

Роутер использует генерацию кода для поиска путей и генерирует класс
`Kaa\Generated\Router\Router`, реализующий `RouterInterface`.

```php
interface RouterInterface{
    /**
     * @param Request $request
     * @return callable(Request): Response $request
     */
    public function findAction(Request $request): callable;
}
```

Пример генерации роутера:

```php
$replacedClasses = new ReplacedClasses();
$sharedConfig = new SharedConfig('./generated');
$config = [
    'scan' => [
      'App\Model',
      'App\Entity',
    ],
    'ignore' => [
      'App\Model\Builtin',
      App\Model\User::class,
    ],
    'prefixes' => [
      'Kaa\SampleProject\Controller\BlogApiController' => '/api/'
    ],
    'routes' => [
        [
            'route' => '/external-api',
            'method' => 'GET',
            'service' => ExternalController::class,
            'method' => 'callExternalApi'
        ],
    ],
];

$validatorGenerator = new RouterGenerator();
$routerGenerator->generate($replacedClasses, $sharedConfig, $config);

// теперь можно использовать сам роутер
$request = Request::initFromGlobals();

/** @var RouterInterface */
$router = new \Kaa\Generated\Router\Router();
$response = $router->findAction($request)($request);
```

### Конфигурация роутера

* `scan` - в данном поле необходимо указать пространства имен, для классов в которых нужно создать правила
  валидации.
* `ignore` - здесь указываются пространства имен или классы, которые нужно игнорировать при генерации валидатора.
* `prefixes` - префиксы, которые будут добавлены к путям в контроллерах или неймспейсах
* `routes` - ручная конфигурация путей. Она переопределит пути, если такие были созданы через атрибуты.

### Определение путей

### Attribute creating

Атрибуты PHP позволяют определять маршруты рядом с кодом контроллеров, связанных с этими маршрутами.

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
    public function healthcheck(Request $request): Response
    {
        // ...
    }
}
```

Эта конфигурация определяет маршрут под названием `healthcheck` который соответствует запросу `GET` для `/blog` URL.
`Router` создаёт объект класса `HealthCheckController` и возвращает ссылку на его метод ```healthcheck```.

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
    public function healthcheck(Request $request): Response
    {
        // ...
    }
}
```

### Настройка маршрутизации для класса

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

### Поддерживаемые методы

Список поддерживаемых аттрибутов:

| Аттрибут | Метод  |
|----------|--------|
| `Route`  | Нет    |
| `Get`    | GET    |
| `Post`   | POST   |
| `Head`   | HEAD   | 
| `Put`    | PUT    |
| `Patch`  | PATCH  |
| `Delete` | DELETE |

### Переменные пути

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

Теперь, если наше приложение получит запрос методом GET с путём `healthcheck/12`,
то во время поиска нужной функции, роутер установит кастомный атрибут в `request`,
у которого имя будет совпадать с тем, что написано в фигурных скобках, а значение со значением.
