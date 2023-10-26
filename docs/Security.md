## Security - библиотека с инструментами безопасности веб приложений

Библиотека предоставляет множество инструментов для и функциональности для реализации
аутентификации, авторизации и контроля доступа в вашем приложении.

### Содержание
- [Security](#security---библиотека-с-инструментами-безопасности-веб-приложений)
  - [Содержание](#содержание)
  - [Общая структура класса `Security`](#общая-структура-класса-security)
  - [Создание объекта класса Security](#создание-объекта-класса-security)
  - [Конфигурация Security](#конфигурация-security)
  - [Проверка ролей пользователя](#проверка-ролей-пользователя)
  - [Пример использования](#пример-использования)
  - [Реализация MyAuthenticator](#реализация-myauthenticator)
  - [Реализация MyUser](#реализация-myuser)

### Общая структура класса `Security`

* `Security` - выполняет основные проверки прав доступа пользователя.

Класс `Security` реализует интерфейс `SecurityInterface`. 
```php
class Security implements SecurityInterface
{
    private callable $getUserFunc;
    private object $user = null
    private array $message = null
    
    public function launch($request): bool
    public function getMessage(): array
    public function getUser(): object
    {
        return $this->getUserFunc();
    }
}
```

* `callable $getUserFunc`  - содержит в себе метод получения пользователя.
* `object $user` - содержит объект пользователя.
* `array $message` - содержит пользовательское сообщение об ошибке.
* `launch($request): bool` - выполняет проверку прав пользователя и возвращает ответ в формате `True` или `False`.
* `getMessage(): array` - возвращает значение поля `$message`
* `getUser(): object` - возвращает объект пользователя. 

### Создание объекта класса Security

Для создания объекта класса `Security` необходимо предварительно сгенерировать класс. За это отвечает 
`SercurityGenerator`, который реализует интерфейс `GeneratorInterface`. Он содержит единственный метод 
`generate(array $congig): void`, который генерирует необходимую часть кода. `array $config` содержит в себе
настройки класса `Security`.

```php
class SecurityGenerator implements GeneratorInterface
{
    public function generate(array $config): void
}
```

### Конфигурация Security

В конфигурации определяются три основных элемента: 
* `providers` - загружает пользователя из какого-либо хранилища. 
* `firewall` - определяет, каким образом будет обрабатываться запрос от пользователя,
в частности, каким образом будет осуществляться аутентификация и авторизация пользователя.
* `instanceCreator` – метод, создающий новые экземпляры классов.
* `access_control` - позволяет вам указать разрешения (кто имеет доступ) и требования 
(какие роли или атрибуты требуются) для определенных URL-адресов.
* `export` - в данном поле указывается основная информация об генерируемом классе `Security`.
* * `directory` - директория для сохранения сгенерированного кода.
* * `namespace` - пространство имен для сгенерированного класса `Security`.
* * `className` - наименование сгенерированного класса.

### Проверка ролей пользователя

Метод `launch(Request $request)` выполняет проверку ролей пользователя, которые указываются в
блоке конфигурации `access_control`. В начале производится попытка найти схожий паттерн в блоке 
`firewall` и получить объект класса пользователя. После производится проверка уровня доступа 
пользователя. Если не удалось получить объект пользователя, то единственный уровень доступа, на
который вернется `True` будет `PUBLIC_ACCESS`.

### Пример использования

```php

class MyAuthenticator implements Kaa\Security\AuthenticatorInterface
{
    public function authenticate(Request $request): callable
    {   
        return fn () => new User($request->get()->get($id));
    }
    
    /**
     * @param Request $request
     * @param callable(): User $getUser
     * @return Response|null
    */
    public function onAuthenticationSuccess(Request $request, callable $getUser): ?Response
    {
        $targetPath = "/profile";
        $response = $this->httpUtils->createRedirectResponse($request, $targetPath);

        return $response;
    }
    
    public function onAuthenticationFailure(Request $request): ?Response
    {
        $targetPath = "/login";
        $response = $this->httpUtils->createRedirectResponse($request, $targetPath);

        return $response;
    }
}

$config = [
    "firewall" => [
        "dev" => [
            "pattern" => "^/(_(profiler|wdt)|css|images|js)/",
            "security" => false,
        ],
        "api" => [
            "pattern" => "^/api/",
            "authenticator" => MyAuthenticator::class,
        ],
    ],
    
    "instanceCreator" => App\IntstanceCreator::create(...),
    
    "access_control" => [
        ["path" => "^/admin", "roles" => "ROLE_ADMIN"],
        ["path" => "^/profile", "roles" => "ROLE_USER"],
    ],
    
    "export" => [
      "directory" => "src\generated",
      "namespace" => "App\Generated",
      "className" => "Security",
    ],
];

new SercurityGenerator::generate($config);

$request = Request::initFromGlobals();

$security = new App\Generated\Security();
$allowed = $security->launch($request);
```

### Реализация MyAuthenticator

Для детальной настройки работы с получением пользователя из какого-либо хранилища, а также с логикой 
успешной и ошибочной авторизации, необходимо создать класс, реализующий интерфейс `Kaa\Security\AuthenticatorInterface`.

```php
namespace Kaa\Security

interface AuthenticatorInterface
{
    public function authenticate(Request $request): callable
    public function onAuthenticationSuccess(Request $request, callable $getUser): ?Response
    public function onAuthenticationFailure(Request $request): ?Response
}
```

Необходимо реализовать три основных метода:
* `authenticate(Request $request): callable` - возвращает lambda-функцию, реализующую получение объекта пользователя.
* `onAuthenticationSuccess(Request $request, callable $getUser): ?Response` - 
содержит логику при успешной авторизации пользователя. Если функция вернет `Response`, то обработка прекратится, если 
вернется `null`, то запрос пойдет дальше по `Router`.
* `onAuthenticationFailure(Request $request): ?Response` - содержит логику безуспешной авторизации. Если функция вернет `Response`, то обработка прекратится, если
  вернется `null`, то запрос пойдет дальше по `Router`.

### Реализация MyUser

Для корректной работы `Security` пользовательский класс пользователя должен реализовывать 
интерфейс `Kaa\Security\UserInterface`.

```php
namespace Kaa\Security

interface User
{
    public function getIdentifier(): string
    public function getRoles(): string[]
}
```

Класс пользователя должен реализовывать несколько обязательных методов:
* `getIdentifier(): string` - возвращает идентификатор пользователя.
* `getRoles(): string[]` - возвращает список ролей пользователя.