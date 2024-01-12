# Security

### Содержание

* [Введение](#введение)
* [Использование генератора Security](#использование-генератора-security)
* [Конфигурация Security](#конфигурация-security)
* [Аутентификаторы](#аутентификаторы)
* [Voter](#voter)

### Введение

Компонент security позволяет легко авторизовать пользователя и проверить его роли.

### Использование генератора Security

Для поддержки различных правил аутентификации и работы с сессиями, `Security` использует генерации кода.
Генератор создаёт класс `Kaa\Generated\Security\Security`, реализующий `SecurityInterface`.

```php
interface SecurityInterface
{
    public function run(Request $request): ?Response;
    
    /**
     * Возвращает пользователя. Если до этого не был вызван метод run(), то выкинет исключение
     */
    public function getUser(): ?UserInterface;
    
    /**
     * Вызывает воутер, чей аттрибут совпадает с переданным и возвращает true, если доступ разрешён
     */
    public function isGranted(string $attribute, string[] $subject = []): bool;
}
```

Поддерживается базовая работа с сессиями через `SessionAuthenticator` и `SessionService`

Пример генерации `Security`:

```php
$sharedConfig = new SharedConfig('./generated');
$config = [
    'scan' => [
        'App\\Voter'
    ],
    
    'firewalls' => [
        'login' => [
            'path' => '^/login$',
            'authenticators' => [
                ['service' => MyLoginAuthenticator::class],
            ],
        ],
        
        'main' => [
            'path' => '.*',
            'authenticators' => [
                ['service' => MyFallbackAuthenticator::class],
            ]    
        ]
    ],
    
    'voters' => [
        'EDIT_POSTS' => ['service' => 'app.post_voter', 'serviceClass', PostVoter::class],
        'VIEW_USERS' => ['service' => UserVoter::class],
    ],
    
    'access_control' => [
        '/api' => ['ROLE_API']
    ],
];

$securityGenerator = new SecurityGenerator();
$securityGenerator->generate($sharedConfig, $config);

// теперь можно использовать сам валидатор

/** @var SecurityInterface */
$security = new \Kaa\Generated\Security\Security();
$response = $security->run(Request::createFromGlobals());
if ($response !== null) {
    $response->send();
    exit;
}

$currentUser = $security->getUser();
```

### Конфигурация Security

* `scan` - неймспейсы, в которых нужно искать классы с атрибутом `#[Voter]`
* `session` - параметры сессии. Если указать этот ключ, то становится доступен аутентификатор `session`
  * `cookie_name` - имя куки, в которой будет хранится имя сессии
  * `lifetime` - время жизни сессии
  * `user_provider` - имя класса/сервиса, реализующего `UserProviderInterface`
* `firewalls` - список фаерволов. Фаерволы матчатся сверху вниз до первого совпадаения пути.
  * `authenticator` - список аутентификаторов. Будет вызван первый анутентификатор, чей метод `supports` вернёт `true`.
* `voters` - ключ - имя аттрибута, значение - имя сервиса воутера
* `access_control` - ограничивает доступ к путям по ролям.

### Аутентификаторы

`AuthenticatorInterface`
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
### Voter

```php
use Kaa\Component\Security\UserInterface;interface VoterInterface
{
    /**
     * @param string[] $subject
     */
    public function vote(array $subject, UserInterface $user): bool;
}

```

Воутеры также могут быть заданы через аттрибуты:
```php

#[Voter('EDIT_POSTS')]
class PostVoter implements VoterInterface
{

}
```
