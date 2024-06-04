# Fast Track Kaa

[TOC]

## Введение

[Kaa](https://git.miem.hse.ru/kaa-framework/kaa/-/tree/main) - фреймворк, предназначенный для разработки приложений на языке KPHP. Существующие фреймворки не поддерживают возможности компиляции в С++ без внесения существенных изменений в код. Kaa призван решить эту проблему. Kaa предоставляет API-интерфейс, схожий с Symfony и другими популярными библиотеками PHP. Однако в местах, где традиционные PHP-фреймворки используют рефлексию, Kaa применяет автоматическую генерацию кода.

**Fast Track Kaa** предназначен для краткого ознакомления с основными возможностями и особенностями работы с Kaa.

## Конфигурация docker-compose.yaml

Для удобства разработки и развертывания будем использовать Docker и Docker Compose. Необходимо настроить контейнеры с **PHP**, **KPHP** и **MySQL** для работы с Kaa.

Для начала напишем Dockerfile для описания контейнера с PHP:

```dockerfile
# Dockerfile.php
FROM php:8.1-apache

# Устанавливаем необходимые PHP-расширения
RUN docker-php-ext-install pdo pdo_mysql

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем файлы проекта в директорию веб-сервера
COPY . /var/www/html/

# Открываем порт 80
EXPOSE 80
```

Теперь напишем Dockerfile для контейнера с KPHP:

```dockerfile
# Dockerfile.kphp
FROM vkcom/kphp:latest

# Устанавливаем рабочую директорию
WORKDIR /app

# Копируем файлы проекта в контейнер
COPY . /app

# Открываем порт 8801
EXPOSE 8801
```

Напишем пример docker-compose.yaml:

```yaml
version: '3.8'

services:
  php:
    build:
      context: .
      dockerfile: Dockerfile.php
    container_name: php-container
    volumes:
      - .:/app
    ports:
      - "8800:80"
    working_dir: /app/public
    entrypoint: php -S 0.0.0.0:80

  kphp:
    build:
      context: .
      dockerfile: Dockerfile.kphp
    container_name: kphp-container
    volumes:
      - .:/app
    ports:
      - "8801:8801"
    working_dir: /app

  database:
    image: mariadb:10.4.21
    container_name: mysql-container
    volumes:
      - ./var/mysql:/var/lib/mysql
    ports:
      - '8805:3306'
    environment:
      MARIADB_ROOT_PASSWORD: secret
      MARIADB_DATABASE: demo
      MARIADB_USER: demo
      MARIADB_PASSWORD: demo
```

### Описание конфигурации

- **php**: Сервис для запуска PHP-приложения.

    - **build**: Собирает образ на основе `Dockerfile.php` в текущей директории.
    - **volumes**: Монтирует текущую директорию в контейнер по пути `/app`.
    - **ports**: Пробрасывает порт 8800 на хосте на порт 80 внутри контейнера.
    - **working_dir**: Устанавливает рабочую директорию контейнера на `/app/public`.
    - **entrypoint**: Запускает встроенный веб-сервер PHP на порту 80.

- **kphp**: Сервис для запуска KPHP-приложения.

    - **build**: Собирает образ на основе `Dockerfile.kphp` в текущей директории.
    - **volumes**: Монтирует текущую директорию в контейнер по пути `/app`.
    - **ports**: Пробрасывает порт 8801 на хосте на порт 8801 внутри контейнера.
    - **working_dir**: Устанавливает рабочую директорию контейнера на `/app`.

- **database**: Сервис для базы данных MariaDB.

    - **image**: Использует образ MariaDB версии 10.4.21.

    - **volumes**: Монтирует директорию `./var/mysql` на хосте в контейнер по пути `/var/lib/mysql`.

    - **ports**: Пробрасывает порт 8805 на хосте на порт 3306 внутри контейнера.

    - **environment**: Устанавливает переменные окружения для настройки базы данных (пароль root, имя базы данных, пользователь и его пароль).

## Установка Kaa и создание правильной структуры проекта

Для установки Kaa можно воспользоваться Composer ([packagist](https://packagist.org/packages/kaa/kaa)):

```bash
composer require kaa/kaa
```

В рабочей директории проекта необходимо придерживаться корректной структуры папок.

* **src/** для содержания основной кодовой базы проекта.
* **config/** содержит в себе все конфигурационные файлы.
* **public/** место для размещения `index.php`.
* **templates/** для хранения всех ваших шаблонов.

## Создание контроллера и конфигурация роутера

Когда приходит HTTP-запрос, например, на главную страницу `http://localhost:8000/`, Kaa пытается найти маршрут, соответствующий пути запроса (`/` в данном случае). Классы, реализующие методы-обработчики путей, называются контроллерами. Маршрут - связующее звено между путем запроса и методом класса, который создает ответ HTTP для этого запроса.

Давайте напишем вместе пример небольшого контроллера:

```php
<?php

namespace App\Controller;

use App\Entity\User;
use App\Model\UserModel;
use App\Model\UserResponseModel;
use App\Service\UserService;
use Kaa\Component\DependencyInjectionDecorator\Inject;
use Kaa\Component\HttpMessage\Response\JsonResponse;
use Kaa\Component\RequestMapperDecorator\AsJsonResponse;
use Kaa\Component\RequestMapperDecorator\MapJsonPayload;
use Kaa\Component\RequestMapperDecorator\MapRouteParameter;
use Kaa\Component\Router\Attribute\Get;
use Kaa\Component\Router\Attribute\Post;

class UserController
{
    #[Post('/admin/user')]
    #[AsJsonResponse]
    public function createUser(
        #[MapJsonPayload]
        UserModel $model,
        #[Inject]
        UserService $userService
    ): int {
        return $userService->createUser($model);
    }

    #[Get('/admin/user/{id}')]
    #[AsJsonResponse]
    public function getUser(
        #[Inject]
        UserService $userService,
        #[MapRouteParameter]
        int $id
    ): UserResponseModel {
        return $userService->getUserById($id);
    }
}
```

В примере мы используем JSON в качестве ответа контроллера. Kaa также предоставляет функционал для работы с HTTP Request/Response.

Для конфигурации роутера необходимо создать конфиг `/config/router.yaml`.

Пример такого конфига:

```yaml
router:
  scan:
    - App\Controller
```

### Описание конфигурации

* **scan** - в данном поле необходимо указать пространства имен, для классов в которых нужно создать правила валидации.

Для более детальной настройки роутера смотрите документацию `Router`.

## Конфигурация БД и описание сущностей

Для корректной работы с БД необходимо добавить конфигурационный файл `/config/database.yaml`.

Пример такой конфигурации:

```yaml
database:
    default:
        driver:
            type: pdo_mysql
            host: database
            database: demo
            user: demo
            password: demo

        scan:
            - App\Entity
```

### Описание конфигурации

* **driver** - описание нашей БД. На данный момент поддерживается только драйвер `pdo_mysql`.
* **scan** - область сканирования наших сущностей.

Также необходимо описать сущности для записи в БД.

Для того чтобы создать класс-представление таблицы базы данных, необходимо создать абстрактный класс, реализующий `EntityInterface` и указать для него атрибут
`Db\Entity`. В него можно передать `$table`, если имя класса не совпадает с названием таблицы:

```php
#[Db\Entity(table: 'NotArticle')]
abstract class Article implements EntityInterface
{

}
```

Пример описания сущности:

```php
<?php

namespace App\Entity;

use Kaa\Component\Database\Attribute as Db;
use Kaa\Component\Database\EntityInterface;

#[Db\Entity]
abstract class Admission implements EntityInterface
{
    #[Db\Id]
    #[Db\Column]
    protected ?int $id = null;

    #[Db\ManyToOne(Topic::class)]
    protected Topic $topic;

    #[Db\ManyToOne(User::class)]
    protected User $user;

    public function getId(): int
    {
        return $this->id ?? -1;
    }

    public function setId(int $id): Admission
    {
        $this->id = $id;
        return $this;
    }

    public function getTopic(): Topic
    {
        return $this->topic;
    }

    public function setTopic(Topic $topic): Admission
    {
        $this->topic = $topic;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }



    public function setUser(User $user): Admission
    {
        $this->user = $user;
        return $this;
    }
}
```

Для более подробного изучения возможностей работы с конфигурацией БД и создания описаний сущностей для нее смотрите документацию `Database`.

## Создание роутов без авторизации

Маршруты создаются двумя путями: указываются в конфигурации роутера или при помощи PHP атрибутов.

Атрибуты позволяют определять маршруты рядом с кодом контроллеров, связанных с этими маршрутами.

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

Эта конфигурация определяет маршрут под названием `healthcheck`, который соответствует запросу `GET` для `/healthcheck` URL.
`Router` создает объект класса `HealthCheckController` и возвращает ссылку на его метод ```healthcheck```.

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

Этот маршрут никак не проверяет авторизованность пользователя. Для проверки авторизации её необходимо предварительно настроить.

## Настройка авторизации

Для проверки авторизации пользователя необходимо написать аутентификатор, который реализует `AuthenticatorInterface`.

```php 
interface AuthenticatorInterface
{
    /**
     * Поддерживает ли аутентификатор обработку этого запроса
     */
    public function supports(Request $request): bool;
    
    /**
     * Проводит аутентификацию и возвращает функцию, которую нужно вызвать, чтобы получить пользователя
     * @return callable(): (UserInterface|null);
     */
    public function authenticate(Request $request): callable;
    
    /**
     * Будет вызвана, если authenticate отработала без ошибок.
     * Может вернуть Response 
     */
    public function onAuthenticationSuccess(Request $request, callable $getUser): ?Response;
    
    /**
     * Будет вызвана, если authenticate выбросила исключение.
     * Может вернуть Response 
     */
    public function onAuthenticationFailure(Request $request, Throwable $throwable): ?Response;
}
```

Давайте теперь напишем пример `LoginAuthenticator`:

```php
<?php

namespace App\Authenticator;

use App\Entity\User;
use App\Service\UserProvider;
use Kaa\Component\Database\EntityManager\EntityManagerInterface;
use Kaa\Component\HttpMessage\Exception\BadRequestException;
use Kaa\Component\HttpMessage\Exception\NoContentException;
use Kaa\Component\HttpMessage\Exception\SuspiciousOperationException;
use Kaa\Component\HttpMessage\Request;
use Kaa\Component\HttpMessage\Response\JsonResponse;
use Kaa\Component\HttpMessage\Response\Response;
use Kaa\Component\Security\AuthenticatorInterface;
use Kaa\Component\Security\Exception\SessionException;
use Kaa\Component\Security\Session\SessionService;
use Random\RandomException;
use RuntimeException;
use Throwable;

class LoginAuthenticator implements AuthenticatorInterface
{
    private SessionService $sessionService;
    private EntityManagerInterface $entityManager;
    private UserProvider $userProvider;

    public function __construct(
        SessionService $sessionService,
        EntityManagerInterface $entityManager,
        UserProvider $userProvider
    ) {
        $this->sessionService = $sessionService;
        $this->entityManager = $entityManager;
        $this->userProvider = $userProvider;
    }

    /**
     * @throws BadRequestException
     * @throws SuspiciousOperationException
     * @throws NoContentException
     */
    public function supports(Request $request): bool
    {
        $content = json_decode($request->getContent(), true);
        return $request->getMethod() === 'POST'
            && array_key_exists('full_name', $content)
            && array_key_exists('profile_id', $content);
    }

    /**
     * @throws NoContentException
     */
    public function authenticate(Request $request): callable
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->entityManager->findOneBy(User::class, ['fullName' => $data['full_name']]);

        if (
            ($data['full_name'] === $user->getFullName())
            && ($data['profile_id'] === $user->getProfile()->getId())
        ) {
            return fn () => $this->userProvider->getUser($user->getIdentifier());
        }
        throw new RuntimeException('Incorrect full_name or profile_id');
    }

    /**
     * @throws SessionException
     * @throws RandomException
     */
    public function onAuthenticationSuccess(Request $request, callable $getUser): ?Response
    {
        $user = $this->entityManager->find(User::class, (int)$getUser()->getIdentifier());
        $user->setLastLoginDate(new \DateTimeImmutable());
        $this->entityManager->flush();

        $cookie = $this->sessionService->writeSession($getUser());

        $response = new Response();
        $response->addCookie($cookie);

        return $response;
    }

    public function onAuthenticationFailure(Request $request, Throwable $throwable): ?Response
    {
        return new JsonResponse(implode(', ', array_keys($this->entityManager->_getManagedEntities())), 401);
    }
}
```

Воутеры можно добавлять при помощи PHP атрибутов:

```php
#[Voter('EDIT_POSTS')]
class PostVoter implements VoterInterface
{

}
```

Их детальная настройка прописывается в конфигурации Security.

## Создание админского роутера и конфигурация Security

Модуль **Security**  позволяет легко проводить авторизацию пользователей и проверку их ролей. Для корректной работы модуля необходимо сформировать конфигурацию `/config/security.yaml`.

Пример конфигурации:

```yaml 
security:
  session:
    cookie_name: x-session
    lifetime: 3600
    user_provider: '@App\Service\UserProvider'

  firewalls:
    register:
      path: '^/register'
      authenticators:
        - { service: App\Authenticator\RegisterAuthenticator }
    login:
      path: '^/login'
      authenticators:
        - { service: App\Authenticator\LoginAuthenticator }

    api:
      path: '.*'
      authenticators:
        - { service: Kaa\Component\Security\Session\SessionAuthenticator }
    
    voters:
      EDIT_POST:
        service: 'app.post_voter'


  access_control:
    ^/admin: [ADMIN]
    ^/moderator: [MODERATOR, ADMIN]
```

### Описание конфигурации

* `scan` - неймспейсы, в которых нужно искать классы с атрибутом `#[Voter]`
* `session` - параметры сессии. Если указать этот ключ, то становится доступен аутентификатор `session`
    * `cookie_name` - имя куки, в которой будет храниться имя сессии
    * `lifetime` - время жизни сессии
    * `user_provider` - имя класса/сервиса, реализующего `UserProviderInterface`
* `firewalls` - список фаерволов. Фаерволы матчатся сверху вниз до первого совпадения пути.
    * `authenticator` - список аутентификаторов. Будет вызван первый аутентификатор, чей метод `supports` вернёт `true`.
* `voters` - ключ - имя атрибута, значение - имя сервиса воутера
* `access_control` - ограничивает доступ к путям по ролям.

## Подписка на on_error через EventListener

Все ошибки, которые возникают во время выполнения вашего кода, отлавливаются фреймворком и передаются в `http.kernel.throwable` event. Для централизованной обработки ошибок можно создать EventListener, который будет подписан на `http.kernel.throwable`.

Пример реализации EventListener:

```php 
<?php

namespace App\EventListener;

use Kaa\Bundle\EventDispatcher\Attribute\EventListener;
use Kaa\Component\HttpKernel\Event\ThrowableEvent;
use Kaa\Component\HttpKernel\HttpKernelEvents;
use Kaa\Component\HttpMessage\Response\JsonResponse;

#[EventListener(HttpKernelEvents::THROWABLE)]
class ThrowableEventListener
{
    public function invoke(ThrowableEvent $event): void
    {
        $event->setResponse(new JsonResponse(not_false(json_encode([
            'message' => $event->getThrowable()->getMessage(),
            'throwable' => get_class($event->getThrowable()),
            'trace' => $event->getThrowable()->getTrace(),
        ]))));
        $event->stopPropagation();
    }
}
```

